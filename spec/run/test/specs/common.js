class CommonValues {
  targetDB = ""
  waiting = 500

  set target(value) {
    this.targetDB = value
  }

  get value_0() {
    return (this.targetDB === "SQLite") ? "0.00" : "0"
  }

  get value_1() {
    return (this.targetDB === "SQLite") ? "1.00" : "1"
  }

  get value_10() {
    return (this.targetDB === "SQLite") ? "10.00" : "10"
  }

  get value_20() {
    return (this.targetDB === "SQLite") ? "20.00" : "20"
  }
  get value_true() {
    return (this.targetDB === "PostgreSQL") ? "true" : "1"
  }
  get value_false() {
    return (this.targetDB === "PostgreSQL") ? "false" : "0"
  }
  get value_bool_0() {
    return (this.targetDB === "PostgreSQL") ? "" : "0"
  }
}

module.exports = CommonValues