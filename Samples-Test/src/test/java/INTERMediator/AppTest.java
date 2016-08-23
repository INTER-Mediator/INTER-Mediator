/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
import org.openqa.selenium.By;
import org.openqa.selenium.By.ByXPath;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.chrome.ChromeDriver;

import java.util.Map;
import java.util.List;
import java.util.HashMap;

@RunWith(JUnit4.class)
public class AppTest {
    private Process p;
    private WebDriver driver;

    private String phpCommand = "php";
    private String phpPort = "8300";
    private String imRoot;

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
    }

    @After
    public void afterTest() {
        this.driver.quit();

        this.p.destroy();
    }

    @Test
    public void samplePageAndNavigation() {
        this.driver.get("http://localhost:" + phpPort + "/Samples/index.html");
        Assert.assertTrue("Sample start page should have a title.",
                this.driver.getTitle().startsWith("INTER-Mediator - Samples"));

        WebElement linkText = this.driver.findElement(By.id("link-1-1"));
        Actions builder = new Actions(this.driver);
        builder.click(linkText);
        builder.build().perform();

        Assert.assertTrue("Sample Form page should have a title.",
                this.driver.getTitle().startsWith("INTER-Mediator - Sample - Form Style/MySQL"));

        this.driver.close();
    }

    @Test
    public void sampleForm() {
        this.driver.get("http://localhost:" + phpPort + "/Samples/index.html");
        WebElement linkText = this.driver.findElement(By.id("link-1-1"));
        Actions builder = new Actions(this.driver);
        builder.click(linkText);
        builder.build().perform();

        Map<String, Integer> keyCounter = new HashMap<String, Integer>();
        Map<String, String> exptectedData = new HashMap<String, String>() {{
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
        try {
            Thread.sleep(1000);
        } catch (Exception e) {

        }

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
        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_info"));
            Assert.assertTrue("Navigation's IM_NAV_info node should be just one.", naviElements.size() == 1);
            String nodeText = naviElements.get(0).getText();
            Assert.assertTrue("The message of Navigation is wrong.", nodeText.startsWith("レコード番号"));
            String[] recCounting = nodeText.substring(6).split("/");
            Assert.assertTrue("The record number is wrong.", Integer.parseInt(recCounting[0].trim()) == 1);
            Assert.assertTrue("The number of records is wrong.", Integer.parseInt(recCounting[1].trim()) == 3);
        }
        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_button"));
            for (WebElement element : naviElements) {
                String elemText = element.getText();
                if (elemText.equals(">")) {
                    builder.click(element);
                    builder.build().perform();
                    break;
                }
            }
        }
        try {
            Thread.sleep(1000);
        } catch (Exception e) {

        }

        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_info"));
            Assert.assertTrue("Navigation's IM_NAV_info node should be just one.", naviElements.size() == 1);
            String nodeText = naviElements.get(0).getText();
            Assert.assertTrue("The message of Navigation is wrong.", nodeText.startsWith("レコード番号"));
            String[] recCounting = nodeText.substring(6).split("/");
            Assert.assertTrue("The record number is wrong.", Integer.parseInt(recCounting[0].trim()) == 2);
            Assert.assertTrue("The number of records is wrong.", Integer.parseInt(recCounting[1].trim()) == 3);
        }
        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_button"));
            for (WebElement element : naviElements) {
                String elemText = element.getText();
                if (elemText.equals(">")) {
                    builder.click(element);
                    builder.build().perform();
                    break;
                }
            }
        }
        try {
            Thread.sleep(1000);
        } catch (Exception e) {

        }

        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_info"));
            Assert.assertTrue("Navigation's IM_NAV_info node should be just one.", naviElements.size() == 1);
            String nodeText = naviElements.get(0).getText();
            Assert.assertTrue("The message of Navigation is wrong.", nodeText.startsWith("レコード番号"));
            String[] recCounting = nodeText.substring(6).split("/");
            Assert.assertTrue("The record number is wrong.", Integer.parseInt(recCounting[0].trim()) == 3);
            Assert.assertTrue("The number of records is wrong.", Integer.parseInt(recCounting[1].trim()) == 3);
        }
        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_button"));
            for (WebElement element : naviElements) {
                String elemText = element.getText();
                if (elemText.equals("<<")) {
                    builder.click(element);
                    builder.build().perform();
                    break;
                }
            }
        }
        try {
            Thread.sleep(1000);
        } catch (Exception e) {

        }
        {
            List<WebElement> naviElements = this.driver.findElements(By.className("IM_NAV_info"));
            Assert.assertTrue("Navigation's IM_NAV_info node should be just one.", naviElements.size() == 1);
            String nodeText = naviElements.get(0).getText();
            Assert.assertTrue("The message of Navigation is wrong.", nodeText.startsWith("レコード番号"));
            String[] recCounting = nodeText.substring(6).split("/");
            Assert.assertTrue("The record number is wrong.", Integer.parseInt(recCounting[0].trim()) == 1);
            Assert.assertTrue("The number of records is wrong.", Integer.parseInt(recCounting[1].trim()) == 3);
        }
    }
}
