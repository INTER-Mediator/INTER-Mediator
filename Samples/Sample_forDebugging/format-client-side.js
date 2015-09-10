function createDisplayFieldsController() {
    var el = document.querySelectorAll("[data-im]");
    for (var i = el.length - 1; i >= 0; i--) {
        createDisplayFields(el[i]);
    }
}

function createDisplayFields(node) {
    var display = false, selector, formatter;
    selector = "[data-im-display='" + node.id + "']";
    formatter = node.getAttribute("data-im-formatter");
    if (document.querySelector(selector)) {
        // show display field
        if (node.nodeName === "INPUT") {
            IMLibElement.setValueToIMNode(document.querySelector(selector), "", node.value, false);
        } else if (node.nodeName === "TEXTAREA") {
            IMLibElement.setValueToIMNode(document.querySelector(selector), "", node.textContent, false);
        }
        document.querySelector(selector).style.display = node.style.display;
        node.style.display = "none";
    } else {
        // create display field
        if (node.nodeName === "INPUT") {
            display = document.createElement("INPUT");
            display.value = node.value;
        } else if (node.nodeName === "TEXTAREA") {
            display = document.createElement("TEXTAREA");
            display.value = node.textContent;
        }
        if (display) {
            display.setAttribute("data-im-format", node.getAttribute("data-im-format"));
            display.setAttribute("data-im-format-options", node.getAttribute("data-im-format-options"));
            node.style.display = "none";
            display.className = (node.className === "") ? "im-display" : node.className + " im-display";
            display.setAttribute("data-im-display", node.id);
            if (node.size) {
                display.size = node.size;
            }
            node.parentNode.insertBefore(display, node);
            if (display.getAttribute("data-im-format") !== "null") {
                IMLibElement.setValueToIMNode(display, "", display.value, false);
                display.addEventListener("click", showInput, false);
            }
        }
    }
}

function showInput() {
    var input = document.getElementById(this.getAttribute("data-im-display"));
    input.style.display = this.style.display;
    this.style.display = "none";
    input.focus();
    input.addEventListener("blur", hideInput, false);
}

function hideInput() {
    createDisplayFields(this);
}
