/**
* main page object containing all methods, selectors and functionality
* that is shared across all page objects
*/
module.exports = class IMPage {
    /**
    * Opens a sub page of the page
    * @param path path of the sub page (e.g. /path/to/page.html)
    */
    open (path) {
        //return browser.url(`http://localhost:9000/vendor/inter-mediator/inter-mediator/${path}`)
        return browser.url(path)
    }

    /**
     * define selectors using getter methods
     */
    get navigator() {
        return $('#IM_NAVIGATOR');
    }

    get navigatorUpdateButton() {
        return $('#IM_NAVIGATOR .IM_NAV_update_button');
    }

    get navigatorInfo() {
        return $('#IM_NAVIGATOR .IM_NAV_info');
    }

    get navigatorMoveButtons() {
        return $$('#IM_NAVIGATOR .IM_NAV_move_button');
    }

    get navigatorMoveButtonFirst() {
        return $$('#IM_NAVIGATOR .IM_NAV_move_button')[0];
    }

    get navigatorMoveButtonPrevious() {
        return $$('#IM_NAVIGATOR .IM_NAV_move_button')[1];
    }

    get navigatorMoveButtonNext() {
        return $$('#IM_NAVIGATOR .IM_NAV_move_button')[2];
    }

    get navigatorMoveButtonLast() {
        return $$('#IM_NAVIGATOR .IM_NAV_move_button')[3];
    }

    get navigatorDeleteButton() {
        return $('#IM_NAVIGATOR .IM_NAV_delete_button');
    }

    get navigatorInsertButton() {
        return $('#IM_NAVIGATOR .IM_NAV_insert_button');
    }

    get navigatorCopyButton() {
        return $('#IM_NAVIGATOR .IM_NAV_copy_button');
    }
}
