<?php

namespace App\Controllers;
/*
PT ITSHOP BISNIS DIGITAL
Website: https://itshop.biz.id
Toko Online: ITSHOP Purwokerto (https://Tokopedia.com/itshoppwt, https://Shopee.co.id/itshoppwt, https://Bukalapak.com/itshoppwt)
Dibuat oleh: Hari Wicaksono, S.Kom
02-2023
*/

use Exception;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Home extends BaseController
{
	public function index()
	{
		// User Agent Class
		$agent = $this->request->getUserAgent();
		if ($agent->isMobile()) {
			return view('home_mobile', []);
		} else {
			return view('home', []);
		}
	}

	public function setLanguage()
	{
		$lang = $this->request->uri->getSegments()[1];
		$this->session->set("lang", $lang);
		return redirect()->back()->with('success', 'Language successfully changed to ' . $lang);
	}

	public function test()
	{
		/**
		 * Install the printer using USB printing support, and the "Generic / Text Only" driver,
		 * then share it (you can use a firewall so that it can only be seen locally).
		 *
		 * Use a WindowsPrintConnector with the share name to print.
		 *
		 * Troubleshooting: Fire up a command prompt, and ensure that (if your printer is shared as
		 * "Receipt Printer), the following commands work:
		 *
		 *  echo "Hello World" > testfile
		 *  copy testfile "\\%COMPUTERNAME%\Receipt Printer"
		 *  del testfile
		 */
		try {
			// Enter the share name for your USB printer here
			//$connector = null;
			$printer = env('printerShareName');
			$connector = new WindowsPrintConnector($printer);

			/* Print a "Hello world" receipt" */
			$printer = new Printer($connector);
			$printer->text("\n");
			$printer->text("\n");
			$printer->text("Hello World!\n");
			$printer->text("\n");
			$printer->text("\n");
			$printer->cut();

			/* Close printer */
			$printer->close();
		} catch (Exception $e) {
			echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
		}
	}

	//--------------------------------------------------------------------

}
