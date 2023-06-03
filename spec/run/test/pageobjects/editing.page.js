const IMPage = require('./im.page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
module.exports = class FormPage extends IMPage {
  get fieldNum1Textfield() {
    return $('._im_test_num1_textfield');
  }

  get fieldId() {
    return $('._im_test_id');
  }

  get fieldNum1Checkbox() {
    return $('._im_test_num1_checkbox');
  }

  get fieldNum1Radio() {
    return $$('._im_test_num1_radio');
  }

  get fieldNum1Popup() {
    return $('._im_test_num1_popup');
  }

  get fieldNum2Textfield() {
    return $('._im_test_num2_textfield');
  }

  get fieldNum2Checkbox() {
    return $('._im_test_num2_checkbox');
  }

  get fieldNum2Radio() {
    return $$('._im_test_num2_radio');
  }

  get fieldNum2Popup() {
    return $('._im_test_num2_popup');
  }

  get fieldDt1Textfield() {
    return $('._im_test_dt1_textfield');
  }

  get fieldDt2Textfield() {
    return $('._im_test_dt2_textfield');
  }

  get fieldDate1Textfield() {
    return $('._im_test_date1_textfield');
  }

  get fieldDate2Textfield() {
    return $('._im_test_date2_textfield');
  }

  get fieldTime1Textfield() {
    return $('._im_test_time1_textfield');
  }

  get fieldTime2Textfield() {
    return $('._im_test_time2_textfield');
  }

  get fieldTs1Textfield() {
    return $('._im_test_ts1_textfield');
  }

  get fieldTs2Textfield() {
    return $('._im_test_ts2_textfield');
  }

  get fieldVc1Textfield() {
    return $('._im_test_vc1_textfield');
  }

  get fieldVc1Checkbox() {
    return $('._im_test_vc1_checkbox');
  }

  get fieldVc1Radio() {
    return $$('._im_test_vc1_radio');
  }

  get fieldVc1Popup() {
    return $('._im_test_vc1_popup');
  }

  get fieldVc1Textarea() {
    return $('._im_test_vc1_textarea');
  }

  get fieldVc2Textfield() {
    return $('._im_test_vc2_textfield');
  }

  get fieldVc2Checkbox() {
    return $('._im_test_vc2_checkbox');
  }

  get fieldVc2Radio() {
    return $$('._im_test_vc2_radio');
  }

  get fieldVc2Popup() {
    return $('._im_test_vc2_popup');
  }

  get fieldVc2Textarea() {
    return $('._im_test_vc2_textarea');
  }

  get fieldText1Textfield() {
    return $('._im_test_text1_textfield');
  }

  get fieldText1Checkbox() {
    return $('._im_test_text1_checkbox');
  }

  get fieldText1Radio() {
    return $$('._im_test_text1_radio');
  }

  get fieldText1Popup() {
    return $('._im_test_text1_popup');
  }

  get fieldText1Textarea() {
    return $('._im_test_text1_textarea');
  }

  get fieldText2Textfield() {
    return $('._im_test_text2_textfield');
  }

  get fieldText2Checkbox() {
    return $('._im_test_text2_checkbox');
  }

  get fieldText2Radio() {
    return $$('._im_test_text2_radio');
  }

  get fieldText2Popup() {
    return $('._im_test_text2_popup');
  }

  get fieldText2Textarea() {
    return $('._im_test_text2_textarea');
  }

  get fieldFloat1Textfield() {
    return $('._im_test_float1_textfield');
  }

  get fieldFloat1Checkbox() {
    return $('._im_test_float1_checkbox');
  }

  get fieldFloat1Radio() {
    return $$('._im_test_float1_radio');
  }

  get fieldFloat1Popup() {
    return $('._im_test_float1_popup');
  }

  get fieldFloat2Textfield() {
    return $('._im_test_float2_textfield');
  }

  get fieldFloat2Checkbox() {
    return $('._im_test_float2_checkbox');
  }

  get fieldFloat2Radio() {
    return $$('._im_test_float2_radio');
  }

  get fieldFloat2Popup() {
    return $('._im_test_float2_popup');
  }

  get fieldDouble1Textfield() {
    return $('._im_test_double1_textfield');
  }

  get fieldDouble1Checkbox() {
    return $('._im_test_double1_checkbox');
  }

  get fieldDouble1Radio() {
    return $$('._im_test_double1_radio');
  }

  get fieldDouble1Popup() {
    return $('._im_test_double1_popup');
  }


  get fieldDouble2Textfield() {
    return $('._im_test_double2_textfield');
  }

  get fieldDouble2Checkbox() {
    return $('._im_test_double2_checkbox');
  }

  get fieldDouble2Radio() {
    return $$('._im_test_double2_radio');
  }

  get fieldDouble2Popup() {
    return $('._im_test_double2_popup');
  }

  get fieldBool1Textfield() {
    return $('._im_test_bool1_textfield');
  }

  get fieldBool1Checkbox() {
    return $('._im_test_bool1_checkbox');
  }

  get fieldBool1Radio() {
    return $$('._im_test_bool1_radio');
  }

  get fieldBool1Popup() {
    return $('._im_test_bool1_popup');
  }

  get fieldBool2Textfield() {
    return $('._im_test_bool2_textfield');
  }

  get fieldBool2Checkbox() {
    return $('._im_test_bool2_checkbox');
  }

  get fieldBool2Radio() {
    return $$('._im_test_bool2_radio');
  }

  get fieldBool2Popup() {
    return $('._im_test_bool2_popup');
  }

}

// module.exports = new FormPage();
