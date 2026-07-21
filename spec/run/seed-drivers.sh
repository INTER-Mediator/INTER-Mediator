#!/usr/bin/env bash
#
# seed-drivers.sh
#
# Work around ESET Cyber Security (and similar macOS endpoint protection) that
# interrupts WebdriverIO's automatic driver downloads. The wdio/node download is
# killed by the real-time / web protection right before the driver binary is
# written, leaving a truncated cache without the executable.
#
# A plain `curl` download is NOT blocked, so this script fetches the matching
# driver(s) with curl and places them in the exact cache paths that WebdriverIO
# checks. wdio then finds the binaries and skips its own (blocked) downloads.
#
# Supported drivers (only for browsers that are actually installed):
#   chrome  -> chromedriver  at <tmp>/chromedriver/<platform>-<ver>/.../chromedriver
#   firefox -> geckodriver   at <tmp>/geckodriver
#   edge    -> edgedriver    at <tmp>/msedgedriver
# where <tmp> is node's os.tmpdir() (the wdio driver cache base).
#
# Idempotent: skips a driver that is already present.
# No-op on non-macOS (CI/Linux is unaffected, wdio downloads there normally).
#
# Usage:
#   bash ./seed-drivers.sh                 # seed all installed browsers' drivers
#   bash ./seed-drivers.sh chrome          # seed only chromedriver
#   bash ./seed-drivers.sh firefox edge    # seed geckodriver and edgedriver
#   bash ./seed-drivers.sh --force         # re-download even if present
#
set -uo pipefail

log()  { echo "[seed-drivers] $*"; }
warn() { echo "[seed-drivers] WARN: $*" >&2; }

os="$(uname -s)"
if [ "$os" != "Darwin" ]; then
  log "Non-macOS ($os): wdio downloads drivers normally here. Nothing to do."
  exit 0
fi
arch="$(uname -m)"

# WebdriverIO uses node's os.tmpdir() as the driver cache base (no cacheDir is
# set in the wdio-*.conf.js files). Resolve it via node so it matches exactly.
TMP_BASE="$(node -e 'process.stdout.write(require("os").tmpdir())')"
if [ -z "${TMP_BASE:-}" ]; then
  warn "Could not resolve node os.tmpdir()."
  exit 1
fi

FORCE=0
declare -a TARGETS=()
for arg in "$@"; do
  case "$arg" in
    --force)                 FORCE=1 ;;
    chrome|firefox|edge|all) TARGETS+=("$arg") ;;
    *) warn "Ignoring unknown argument: $arg (use: --force chrome firefox edge all)" ;;
  esac
done
if [ "${#TARGETS[@]}" -eq 0 ]; then
  TARGETS=(all)
fi
for t in "${TARGETS[@]}"; do
  if [ "$t" = "all" ]; then
    TARGETS=(chrome firefox edge)
    break
  fi
done

url_exists() { curl -sfIL "$1" >/dev/null 2>&1; }

# --- chromedriver (Google Chrome) -------------------------------------------
seed_chromedriver() {
  local app="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
  if [ ! -x "$app" ]; then
    log "Google Chrome not installed; skipping chromedriver."
    return 0
  fi
  local cft wdiop zipdir
  case "$arch" in
    arm64)  cft="mac-arm64"; wdiop="mac_arm"; zipdir="chromedriver-mac-arm64" ;;
    x86_64) cft="mac-x64";   wdiop="mac";     zipdir="chromedriver-mac-x64"   ;;
    *) warn "Unsupported architecture: $arch"; return 1 ;;
  esac
  local ver
  ver="$("$app" --version | grep -oE '[0-9]+(\.[0-9]+){3}' | head -n1)"
  if [ -z "$ver" ]; then warn "Could not determine Chrome version."; return 1; fi

  local version_dir="${TMP_BASE}/chromedriver/${wdiop}-${ver}"
  local dest_dir="${version_dir}/${zipdir}"
  local bin="${dest_dir}/chromedriver"
  if [ "$FORCE" -eq 0 ] && [ -x "$bin" ]; then
    log "chromedriver already seeded ($ver): $bin"
    return 0
  fi

  local url="https://storage.googleapis.com/chrome-for-testing-public/${ver}/${cft}/chromedriver-${cft}.zip"
  if ! url_exists "$url"; then
    warn "chromedriver for the exact Chrome version $ver is not published yet:"
    warn "  $url"
    warn "This can happen briefly after a Chrome auto-update; retry later."
    return 1
  fi

  local zip; zip="$(mktemp -t chromedriver-seed).zip"
  log "Downloading chromedriver $ver ..."
  if ! curl -sSL -o "$zip" "$url"; then warn "chromedriver download failed."; rm -f "$zip"; return 1; fi
  rm -rf "$version_dir"; mkdir -p "$version_dir"
  if ! unzip -oq "$zip" -d "$version_dir"; then warn "chromedriver unzip failed."; rm -f "$zip"; return 1; fi
  rm -f "$zip"
  xattr -cr "$dest_dir" 2>/dev/null || true
  chmod +x "$bin" 2>/dev/null || true
  if [ -x "$bin" ]; then log "Seeded chromedriver $ver: $bin"; return 0; fi
  warn "chromedriver missing after seeding at $bin"; return 1
}

# --- geckodriver (Mozilla Firefox) ------------------------------------------
seed_geckodriver() {
  local app="/Applications/Firefox.app/Contents/MacOS/firefox"
  if [ ! -x "$app" ]; then
    log "Mozilla Firefox not installed; skipping geckodriver."
    return 0
  fi
  # The geckodriver package places the binary directly at <cacheDir>/geckodriver.
  local bin="${TMP_BASE}/geckodriver"
  if [ "$FORCE" -eq 0 ] && [ -x "$bin" ]; then
    log "geckodriver already seeded: $bin"
    return 0
  fi
  local suffix
  case "$arch" in
    arm64)  suffix="macos-aarch64" ;;
    x86_64) suffix="macos" ;;
    *) warn "Unsupported architecture: $arch"; return 1 ;;
  esac
  # Resolve the latest geckodriver version from the same source the package uses.
  local ver
  ver="$(curl -sfL "https://raw.githubusercontent.com/mozilla/geckodriver/release/Cargo.toml" \
        | grep -E '^version = ' | head -n1 | sed -E 's/.*"([^"]+)".*/\1/')"
  if [ -z "$ver" ]; then warn "Could not resolve the latest geckodriver version."; return 1; fi

  local url="https://github.com/mozilla/geckodriver/releases/download/v${ver}/geckodriver-v${ver}-${suffix}.tar.gz"
  if ! url_exists "$url"; then warn "geckodriver $ver not found: $url"; return 1; fi

  local tmpd; tmpd="$(mktemp -d -t geckodriver-seed)"
  log "Downloading geckodriver $ver ..."
  if ! curl -sSL -o "${tmpd}/geckodriver.tar.gz" "$url"; then warn "geckodriver download failed."; rm -rf "$tmpd"; return 1; fi
  if ! tar -xzf "${tmpd}/geckodriver.tar.gz" -C "$tmpd"; then warn "geckodriver extract failed."; rm -rf "$tmpd"; return 1; fi
  if [ ! -f "${tmpd}/geckodriver" ]; then warn "geckodriver binary not found in archive."; rm -rf "$tmpd"; return 1; fi
  mkdir -p "$TMP_BASE"
  cp "${tmpd}/geckodriver" "$bin"
  rm -rf "$tmpd"
  xattr -c "$bin" 2>/dev/null || true
  chmod +x "$bin" 2>/dev/null || true
  if [ -x "$bin" ]; then log "Seeded geckodriver $ver: $bin"; return 0; fi
  warn "geckodriver missing after seeding at $bin"; return 1
}

# --- edgedriver (Microsoft Edge) --------------------------------------------
seed_edgedriver() {
  local app="/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge"
  if [ ! -x "$app" ]; then
    log "Microsoft Edge not installed; skipping edgedriver."
    return 0
  fi
  # The edgedriver package places the binary directly at <cacheDir>/msedgedriver.
  local bin="${TMP_BASE}/msedgedriver"
  if [ "$FORCE" -eq 0 ] && [ -x "$bin" ]; then
    log "edgedriver already seeded: $bin"
    return 0
  fi
  local name
  case "$arch" in
    arm64)  name="edgedriver_mac64_m1" ;;
    x86_64) name="edgedriver_mac64" ;;
    *) warn "Unsupported architecture: $arch"; return 1 ;;
  esac
  local ver
  ver="$("$app" --version | grep -oE '[0-9]+(\.[0-9]+){3}' | head -n1)"
  if [ -z "$ver" ]; then warn "Could not determine Microsoft Edge version."; return 1; fi

  local url="https://msedgedriver.microsoft.com/${ver}/${name}.zip"
  if ! url_exists "$url"; then
    warn "edgedriver $ver not found; trying LATEST_RELEASE_<major>_MACOS ..."
    local major; major="$(printf '%s' "$ver" | cut -d. -f1)"
    local altver
    altver="$(curl -sfL "https://msedgedriver.microsoft.com/LATEST_RELEASE_${major}_MACOS" \
             | tr -d '\000\r' | grep -oE '[0-9]+(\.[0-9]+){3}' | head -n1)"
    if [ -z "$altver" ]; then warn "Could not resolve an alternative edgedriver version."; return 1; fi
    ver="$altver"
    url="https://msedgedriver.microsoft.com/${ver}/${name}.zip"
    if ! url_exists "$url"; then warn "edgedriver $ver not found: $url"; return 1; fi
  fi

  local tmpd; tmpd="$(mktemp -d -t edgedriver-seed)"
  log "Downloading edgedriver $ver ..."
  if ! curl -sSL -o "${tmpd}/edgedriver.zip" "$url"; then warn "edgedriver download failed."; rm -rf "$tmpd"; return 1; fi
  if ! unzip -oq "${tmpd}/edgedriver.zip" -d "$tmpd"; then warn "edgedriver unzip failed."; rm -rf "$tmpd"; return 1; fi
  if [ ! -f "${tmpd}/msedgedriver" ]; then warn "msedgedriver binary not found in archive."; rm -rf "$tmpd"; return 1; fi
  mkdir -p "$TMP_BASE"
  cp "${tmpd}/msedgedriver" "$bin"
  rm -rf "$tmpd"
  xattr -c "$bin" 2>/dev/null || true
  chmod +x "$bin" 2>/dev/null || true
  if [ -x "$bin" ]; then log "Seeded edgedriver $ver: $bin"; return 0; fi
  warn "edgedriver missing after seeding at $bin"; return 1
}

failures=0
for t in "${TARGETS[@]}"; do
  case "$t" in
    chrome)  seed_chromedriver || failures=$((failures + 1)) ;;
    firefox) seed_geckodriver  || failures=$((failures + 1)) ;;
    edge)    seed_edgedriver   || failures=$((failures + 1)) ;;
  esac
done

if [ "$failures" -gt 0 ]; then
  warn "$failures driver(s) could not be seeded (see messages above)."
  exit 1
fi
log "All requested drivers are ready."
exit 0
