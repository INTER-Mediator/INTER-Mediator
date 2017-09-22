/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 * <p>
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link https://inter-mediator.com/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

package INTERMediator;

import org.junit.Assert;
import org.junit.runner.RunWith;
import org.junit.runners.JUnit4;
import org.junit.Test;
import org.junit.After;
import org.junit.Before;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import com.thoughtworks.selenium.Wait;
import org.openqa.selenium.By;
import org.openqa.selenium.By.ByXPath;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.ExpectedConditions.*;
import org.openqa.selenium.support.ui.ExpectedCondition;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.openqa.selenium.support.ui.ExpectedConditions;

import java.util.Map;
import java.util.List;
import java.util.HashMap;

public class IMAppTestSupport {
    protected Process p;
    protected WebDriver driver;
    protected Actions builder;


    protected String phpCommand = "php";
    protected String phpPort = "8300";
    protected String imRoot;

    @Before
    public void beforeTest() {
        // Recognized INTER-Mediator root directory
        String path = System.getProperty("user.dir");   // Detect current directory
        String[] pathComp = path.split("/", -1);
        StringBuffer targetPath = new StringBuffer("");
        for (int i = 0; i < pathComp.length; i++) {
            targetPath.append("/");
            targetPath.append(pathComp[i]);
            if (pathComp[i].equals("INTER-Mediator")) {
                this.imRoot = targetPath.toString();
                break;
            }
        }

        // Start up the PHP server mode.
        try {
            String serverCmd = this.phpCommand + " -S localhost:" + phpPort + " -t " + this.imRoot;
            this.p = Runtime.getRuntime().exec(serverCmd);
        } catch (Exception e) {
        }

// Please don't remove the following commentted lines. These are memo of the requirements of a system property.
//      System.setProperty("webdriver.chrome.driver",
//           "/Users/msyk/Documents/INTER-Mediator_develop/chromedriver");
// The Chrome Driver requres the path of server binary as a system property.
// This is the property name and sample value.
//      webdriver.chrome.driver=/Users/msyk/Documents/INTER-Mediator_develop/chromedriver

        this.driver = new ChromeDriver();
        this.builder = new Actions(this.driver);
    }

    @After
    public void afterTest() {
        this.driver.quit();

        this.p.destroy();
    }

    protected void imWaitPageGeneration() {
        (new WebDriverWait(driver, 30))
                .until(ExpectedConditions.presenceOfElementLocated(By.id("IM_CREDIT")));
    }

    private int[] imNavigationInfo() throws Exception {
        List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_info"));
        if (naviElements.size() != 1) {
            throw new Exception("The Navagation is rendered wrong.");
        }
        String nodeText = naviElements.get(0).getText();
        if (! nodeText.startsWith("レコード番号") && ! nodeText.startsWith("Record #")) {
            throw new Exception("The message of Navigation is wrong.");
        }
        for (int i=0;i<nodeText.length();i++)   {
            char c = nodeText.charAt(i);
            if (c >='0' && c<='9')  {
                nodeText = nodeText.substring(i);
                break;
            }
        }
        String[] recCounting = nodeText.split("/");
        int[] result = new int[2];
        result[0] = Integer.parseInt(recCounting[0].trim());
        result[1] = Integer.parseInt(recCounting[1].trim());
        return result;
    }

    protected int imNavigationRecordNumber() throws Exception {
        try {
            return imNavigationInfo()[0];
        } catch (Exception e) {
            throw e;
        }
    }

    protected int imNavigationRecordCount() throws Exception {
        try {
            return imNavigationInfo()[1];
        } catch (Exception e) {
            throw e;
        }
    }

    protected void imCheckShowingErrorInfo() {
        WebElement errorPanel = this.driver.findElement(By.id("_im_error_panel_4873643897897"));
        if (errorPanel != null) {
            Assert.assertTrue("Error messages are shown on page.", false);
        }
    }

    protected void imClickNavigationButton(String buttonLabel) {
        List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_button"));
        for (WebElement element : naviElements) {
            String elemText = element.getText();
            if (elemText.equals(buttonLabel)) {
                this.builder.click(element);
                this.builder.build().perform();
                return;
            }
        }
    }

    protected void imClickElementById(String idValue) {
        WebElement linkText = this.driver.findElement(By.id(idValue));
        this.builder.click(linkText);
        this.builder.build().perform();
    }

    protected void imCompareBindingAndRecord(Map<String, String> exptectedData)  {
        Map<String, Integer> keyCounter = new HashMap<String, Integer>();
        List<WebElement> elements = this.driver.findElements(By.tagName("input"));
        elements.addAll(this.driver.findElements(By.tagName("select")));
        elements.addAll(this.driver.findElements(By.tagName("textarea")));
        for (WebElement element : elements) {
            String dataIM = element.getAttribute("data-im");
            String elemValue;
            if (dataIM != null) {
                int counter;
                if (keyCounter.get(dataIM) == null) {
                    counter = 1;
                } else {
                    counter = keyCounter.get(dataIM) + 1;
                }
                keyCounter.put(dataIM, counter);

                String elemTag = element.getTagName();
                String elemType = element.getAttribute("type");
                if (elemTag.equalsIgnoreCase("select")) {
                    elemValue = element.getAttribute("value");
                } else if (elemTag.equalsIgnoreCase("textarea")) {
                    elemValue = element.getText();
                } else { // input tag elements
                    if (elemType.equalsIgnoreCase("checkbox") || elemType.equalsIgnoreCase("radio")) {
                        elemValue = element.isSelected() ? element.getAttribute("value") : "";
                    } else {
                        elemValue = element.getAttribute("value");
                    }
                }

                Assert.assertTrue("The bond value to the element is not same as the field value:　" +
                                "data-im=" + dataIM + ", counter=" + counter + ", element=" + elemValue +
                                ", expected=" + exptectedData.get(dataIM + "." + counter),
                        exptectedData.get(dataIM + "." + counter).equals(elemValue));
            }
        }
    }

    protected Map<String, String> imTestDataPerson(int recordNumber) {
        if (recordNumber == 1) {
            return new HashMap<String, String>() {{
                put("person@checking.1", "");
                put("person@name.1", "Masayuki Nii");
                put("person@mail.1", "msyk@msyk.net");
                put("person@category.1", "");
                put("person@memo.1", "");
                put("person@location.1", "");
                put("person@location.2", "");
                put("person@location.3", "");
                put("person@location.4", "");
                put("contact@datetime.1", "2009-12-01 15:23:00");
                put("contact@summary.1", "Telephone");
                put("contact@important.1", "");
                put("contact@way.1", "4");
                put("contact@kind.1", "4");
                put("contact@description@innerHTML.1", "");
                put("contact@datetime.2", "2009-12-02 15:23:00");
                put("contact@summary.2", "Meeting");
                put("contact@important.2", "1");
                put("contact@way.2", "4");
                put("contact@kind.2", "7");
                put("contact@description@innerHTML.2", "");
                put("contact@datetime.3", "2009-12-03 15:23:00");
                put("contact@summary.3", "Mail");
                put("contact@important.3", "");
                put("contact@way.3", "5");
                put("contact@kind.3", "8");
                put("contact@description@innerHTML.3", "");
                put("history@startdate.1", "2001-04-01");
                put("history@enddate.1", "2003-03-31");
                put("history@description.1", "Hight School");
                put("history@startdate.2", "2003-04-01");
                put("history@enddate.2", "2007-03-31");
                put("history@description.2", "University");
            }};
        }
        return null;
    }
}
