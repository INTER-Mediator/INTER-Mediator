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

@RunWith(JUnit4.class)
public class SampleAppTest extends IMAppTestSupport {

    @Test
    public void samplePageAndNavigation() {
        // Show the index page of samples
        this.driver.get("http://localhost:" + phpPort + "/Samples/index.html");
        // Check the page title
        Assert.assertTrue("Sample start page should have a title.",
                this.driver.getTitle().startsWith("INTER-Mediator - Samples"));
        // Move to SampleForm
        this.imClickElementById("link-1-1");
        this.imWaitPageGeneration();    // Wait for generating page.
        // Check the page title
        Assert.assertTrue("Sample Form page should have a title.",
                this.driver.getTitle().startsWith("INTER-Mediator - Sample - Form Style/MySQL"));
    }

    @Test
    public void sampleForm() {
        // Show the index page of samples
        this.driver.get("http://localhost:" + phpPort + "/Samples/index.html");
        // Move to SampleForm
        this.imClickElementById("link-1-1");
        this.imWaitPageGeneration();    // Wait for generating page.

        try {
            // There should be 3 records and show the first one.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 1);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);
            // At the start of SampleForm, the page should show the first record of person table.
            this.imCompareBindingAndRecord(this.imTestDataPerson(1));

            // Navigate to the next record.
            this.imClickNavigationButton(">");
            this.imWaitPageGeneration();
            // There should be 3 records and show the second one.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 2);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);

            // Navigate to the next record.
            this.imClickNavigationButton(">");
            this.imWaitPageGeneration();    // Wait for generating page.
            // There should be 3 records and show the third one.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 3);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);

            // Navigate to the previous record.
            this.imClickNavigationButton("<");
            this.imWaitPageGeneration();    // Wait for generating page.
            // There should be 3 records and show the second one.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 2);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);

            // Navigate to the first record.
            this.imClickNavigationButton("<<");
            this.imWaitPageGeneration();    // Wait for generating page.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 1);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);

            // Navigate to the last record.
            this.imClickNavigationButton(">>");
            this.imWaitPageGeneration();    // Wait for generating page.
            Assert.assertTrue("The record number is wrong.", this.imNavigationRecordNumber() == 3);
            Assert.assertTrue("The number of records is wrong.", this.imNavigationRecordCount() == 3);
        } catch (Exception e)   {
            Assert.assertTrue(e.getMessage(), false);
        }
     }

}
