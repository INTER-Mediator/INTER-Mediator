require "selenium-webdriver"

describe "INTER-Mediator-Server VM" do
  before do
     @webdriver = Selenium::WebDriver.for :firefox
   end

  it "A title of the top page is 'INTER-Mediator 5.6-dev - VM for Trial'" do
    @webdriver.navigate.to "http://127.0.0.1/"
    expect(@webdriver.title).to eq("INTER-Mediator 5.6-dev - VM for Trial")
  end

  after do
    @webdriver.quit
  end
end
