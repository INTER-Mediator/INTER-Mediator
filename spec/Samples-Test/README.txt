About "Samples-Test"

Masayuki Nii [nii@msyk.net]
2016-08-22

The "Samples-Test" directory contains test codes for same web pages in Samples with Selenium Web Driver.
You can get how to prepare and test with Samples by Selenium as below.

1. Setup Apache-Maven at the out side of "INTER-Mediator" directory.
--------------------------------------------------------------------
1-1. Download from this site: https://maven.apache.org/download.cgi.
1-2. You might get the file "apache-maven-3.3.9-bin.zip" and extract as the directory "apache-maven-3.3.9."
1-3. Move the "apache-maven-3.3.9" directory to right place.
1-4. Add the path to apache-maven-3.3.9/bin directory, and set up some environmental variables.
  (OS X) Add this line to ~/.bash_profile. M2_HOME should be your Maven directory
    M2_HOME="/path to your maven directory/apache-maven-3.3.9"
    PATH="$PATH:$M2_HOME/bin"
  (Windows) Working with Control Panel
    Navigate "System and Security" > "System" > "Detail" of "System Property" > "Envrionment Variables" button.
    The lower side of "Envrionment Variables" dialog box is the list of "System Envrionment Variables."
    Add the item "M2_HOME" to "System Envrionment Variables" and set the valid path.
    Open the item "Path" by double-clicking and add the line "%M2_HOME%\bin"
1-5. Clarify the JAVA_HOME environment variable is defined and right.
  (OS X) Add this line to ~/.bash_profile.
    JAVA_HOME="$(/usr/libexec/java_home)"
  (Windows) Add the item "JAVA_HOME" to the "Envrionment Variables" dialog box.

2. Setup PHP to work as a command
---------------------------------
Clarify the "php" command works on your PC/Mac. The PHP installers usually add the command line tools.
macOS includes php command initially.

3. Setup Chrome Driver Server.
------------------------------
3-1. Go to this site: http://chromedriver.storage.googleapis.com/index.html.
3-2. Select appropriate (i.e. latest) version as like "2.23."
3-3. Download the "chromedriver_xxxxx_zip" file as reagarding to your platform.
3-4. Extract and move the "chromedriver" file to right place.
3-5. Recognize the path to the "chromedriver" file.

4. Test
--------
4-1. Set the "INTER-Mediator" directory as your current directory.
4-2. Do the command "cd Samples-Test." This means INTER-Mediator/Samples-Test should be a current.
4-3. Start the test! by the following command. How about that?
  mvn test -Dwebdriver.chrome.driver=<<The path of Step 2-5>>

Rererence:
Selenium WebDriver Speedrun Installs,
http://seleniumsimplified.com/speedrun-installs/

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