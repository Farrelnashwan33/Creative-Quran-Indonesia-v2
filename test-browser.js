import puppeteer from 'puppeteer';

(async () => {
  console.log("Launching headless browser...");
  let browser;
  try {
    browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();
    
    page.on('console', msg => {
      console.log(`[CONSOLE ${msg.type().toUpperCase()}]: ${msg.text()}`);
    });
    
    page.on('pageerror', err => {
      console.error('[BROWSER EXCEPTION]:', err.toString());
      if (err.stack) {
        console.error(err.stack);
      }
    });

    console.log("Navigating to http://localhost:5173/ ...");
    await page.goto('http://localhost:5173/', { waitUntil: 'networkidle0', timeout: 10000 });
    
    console.log("Waiting 3 seconds to capture any delayed client-side errors...");
    await new Promise(resolve => setTimeout(resolve, 3000));
  } catch (error) {
    console.error("Test execution failed:", error);
  } finally {
    if (browser) {
      await browser.close();
      console.log("Browser closed.");
    }
    process.exit(0);
  }
})();
