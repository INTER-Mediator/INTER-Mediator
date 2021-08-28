#   How to test using Rspec and Selenium WebDriver with INTER-Mediator-Server VM
#   - Install Ruby on the host of VM (You don't need installing Ruby on macOS usually)
#   - Install gem of rspec and selenium-webdriver on the host of VM ("gem install rspec selenium-webdriver")
#   - Install Firefox and geckodriver (Chrome and chromedriver) on the host of VM
#   - Change directory to the root directory of INTER-Mediator on the host of VM
#   - Run "ADDR=192.168.56.101 BROWSER=chrome rspec --default-path=dist-docs/docker/spec -f doc -c dist-docs/docker/spec/samples/samples_spec.rb" on the host of VM

require "selenium-webdriver"

describe "INTER-Mediator-Server VM" do
  before do
    @protocol = 'http'
    @port = 80
    if ENV['PROTOCOL'] == 'https'
      @protocol = 'https'
      @port = 443
    end
    if ENV['PORT'] != nil
      @port = ENV['PORT']
    end
    @port = @port.to_s
    @addr = '127.0.0.1'
    if ENV['ADDR'] != nil
      @addr = ENV['ADDR']
    end
    @browser = 'firefox'
    if ENV['BROWSER'] != nil
      @browser = ENV['BROWSER'].downcase
    end
    if @browser == 'firefox'
      profile = Selenium::WebDriver::Firefox::Profile.new
      profile['intl.accept_languages'] = "en-US, en"
      profile['general.useragent.locale'] = "en-US"
      options = Selenium::WebDriver::Firefox::Options.new
      options.profile = profile
      @driver = Selenium::WebDriver.for :firefox, options: options
    elsif @browser == 'chrome'
      options = Selenium::WebDriver::Chrome::Options.new
      options.add_preference('intl.accept_languages', 'en-US, en')
      options.add_preference('general.useragent.locale', 'en-US')
      options.add_argument('--headless')
      options.add_argument('--disable-gpu')
      options.add_argument('--disable-dev-shm-usage')
      options.add_argument('--no-sandbox')
      options.add_argument('--window-size=1280,800')
      @driver = Selenium::WebDriver.for :chrome, options: options
    end
    @driver.navigate.to "http://" + @addr + "/"
    @wait = Selenium::WebDriver::Wait.new(:timeout => 15)
    @driver.manage.timeouts.page_load = 10
  end

  it "The title of the first page should be 'INTER-Mediator 9 - VM for Trial'." do
    expect(@driver.title).to eq("INTER-Mediator 9 - VM for Trial")
  end

  it "Page File Editor should be working" do
    range = 1..40
    range.each{|num|
      @driver.navigate.to "http://" + @addr + "/"
      @wait.until {
        element = @driver.find_element(:xpath, "//a[contains(@href, 'pageedit.html?target=../../page" + "%02d" % num + ".html')]")
        script = "return arguments[0].removeAttribute('target')"
        @driver.execute_script(script, element) 
        element.click
        sleep 2
        #expect(@driver.title).to eq("Page File Editor: ../../page" + "%02d" % num + ".html")
        expect(@driver.title).to include("Page File Editor")
      }
    }
  end

  it "Definition File Editor should be working" do
    range = 1..40
    range.each{|num|
      @driver.navigate.to "http://" + @addr + "/"
      @wait.until {
        element = @driver.find_element(:xpath, "//a[contains(@href, 'defedit.html?target=../../def" + "%02d" % num + ".php')]")
        script = "return arguments[0].target = ''"
        @driver.execute_script(script, element)
        element.click
        sleep 2
        #expect(@driver.title).to eq("Definition File Editor: ../../def" + "%02d" % num + ".php")
        expect(@driver.title).to include("Definition File Editor")
      }
    }
  end
  
  it "The path of 'Sample Program' should be '/INTER-Mediator/samples/'." do
    element = @driver.find_element(:xpath, "//a[contains(@href, 'samples')]")
    expect(element.attribute("href")).to eq("http://" + @addr + "/INTER-Mediator/samples/")
    @driver.navigate.to element.attribute("href")
    expect(@driver.title).to eq("INTER-Mediator - Samples")
  end

  it "Practice 'search(no JavaScript)' for MySQL/MariaDB should be working" do
    @driver.navigate.to "http://" + @addr + "/INTER-Mediator/samples/"
    @wait.until {
      element = @driver.find_element(:xpath, "//a[contains(@href, 'Practices/search_page1.html')]")
      element.click
      #@driver.navigate.to "http://" + @addr + "/INTER-Mediator/samples/Practices/search_page1.html"
      sleep 1
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements.size).to eq(4)
      expect(elements[0].text).to eq("1000000")
      expect(elements[1].text).to eq("1020072")
      expect(elements[3].text).to eq("1010032")

      element = @driver.find_element(:id, "_im_progress")
      expect(element.attribute("style")).to eq("opacity: 0; z-index: -9999; transition-duration: 0.3s;")

      Selenium::WebDriver::Support::Select.new(@driver.find_element(:xpath, "//select[@data-im='_@limitnumber:postalcode']")).select_by(:value, "4")
      sleep 1
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements.size).to eq(4)
      element = @driver.find_element(:xpath, "//span[@class='IM_NAV_info']")
      expect(element.text).to include("1-4 / 3654")

      Selenium::WebDriver::Support::Select.new(@driver.find_element(:xpath, "//select[@data-im='_@limitnumber:postalcode']")).select_by(:value, "10")
      sleep 1
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements.size).to eq(10)
      element = @driver.find_element(:xpath, "//span[@class='IM_NAV_info']")
      expect(element.text).to include("1-10 / 3654")

      Selenium::WebDriver::Support::Select.new(@driver.find_element(:xpath, "//select[@data-im='_@limitnumber:postalcode']")).select_by(:value, "40")
      sleep 1
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements.size).to eq(30)
      element = @driver.find_element(:xpath, "//span[@class='IM_NAV_info']")
      expect(element.text).to include("1-30 / 3654")
    }
  end

#   it "Sample 'file upload' for MySQL/MariaDB should be working" do
#     @driver.navigate.to "http://" + @addr + "/INTER-Mediator/samples/"
#     @wait.until {
#       element = @driver.find_element(:xpath, "//a[contains(@href, 'Sample_webpage/fileupload_MySQL.html')]")
#       element.click
#       sleep 1
#       element = @driver.find_element(:id, "IM_InsertButton_2-3")
#       element.click
#       sleep 1
#       element = @driver.find_element(:xpath, "//td[@data-im='testtable@vc1']")
#       expect(element.find_element(:tag_name, "div").text).to eq("Drag Here.")
#     }
#   end

  after do
    @driver.quit
  end
end
