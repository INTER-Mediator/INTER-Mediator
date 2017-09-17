require "selenium-webdriver"

describe "INTER-Mediator-Server VM" do
  before do
    @driver = Selenium::WebDriver.for :firefox
    @driver.navigate.to "http://127.0.0.1/"
    @wait = Selenium::WebDriver::Wait.new(:timeout => 15)
  end

  it "The title of the first page should be 'INTER-Mediator 5.7-dev - VM for Trial'." do
    expect(@driver.title).to eq("INTER-Mediator 5.7-dev - VM for Trial")
  end

  it "The URL of 'Sample Program' should be 'http://127.0.0.1/INTER-Mediator/Samples/'." do
    element = @driver.find_element(:xpath, "//a[contains(@href, 'Samples')]")
    expect(element.attribute("href")).to eq("http://127.0.0.1/INTER-Mediator/Samples/")
    @driver.navigate.to element.attribute("href")
    expect(@driver.title).to eq("INTER-Mediator - Samples")
  end

  it "Practice 'search(no JavaScript)' for MySQL/MariaDB should be working" do
    @driver.navigate.to "http://127.0.0.1/INTER-Mediator/Samples/"
    @wait.until {
      element = @driver.find_element(:xpath, "//a[contains(@href, 'Practices/search_page1.html')]")
      element.click
      #@driver.navigate.to "http://127.0.0.1/INTER-Mediator/Samples/Practices/search_page1.html"
      sleep 2
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements[0].text).to eq("1000000")
      expect(elements[1].text).to eq("1020072")
      expect(elements[19].text).to eq("1006812")

      element = @driver.find_element(:id, "_im_progress")
      expect(element.attribute("style")).to eq("opacity: 0; display: flex; z-index: -9999; transition-duration: 0.3s;")
    }
  end

  it "Practice 'search(using JavaScript)' for MySQL/MariaDB should be working" do
    @driver.navigate.to "http://127.0.0.1/INTER-Mediator/Samples/"
    @wait.until {
      element = @driver.find_element(:xpath, "//a[contains(@href, 'Practices/search_page2.html')]")
      element.click
      #@driver.navigate.to "http://127.0.0.1/INTER-Mediator/Samples/Practices/search_page2.html"
      sleep 2
      elements = @driver.find_elements(:xpath, "//div[@data-im='postalcode@f3']")
      expect(elements[0].text).to eq("1000000")
      expect(elements[1].text).to eq("1020072")
      expect(elements[19].text).to eq("1006812")

      element = @driver.find_element(:id, "_im_progress")
      expect(element.attribute("style")).to eq("opacity: 0; display: flex; z-index: -9999; transition-duration: 0.3s;")
    }
  end

  after do
    @driver.quit
  end
end
