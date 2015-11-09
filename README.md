# Isiweb's magestpay
Magento Plugin For GestPay (Banca Sella) payment gateway

This is a derivative work from Daniele Gagliardi's work which I found a lot improvable.

For the original work please see http://blog.danielegagliardi.it/modulo-banca-sella-gestpay-gratis/

What it provides
<ul>
<li>Allows linking to Banca Sella's Gestpay gateway system</li>
<li>Provides correct translation among currency codes used by Magento and the ones used by Gestpay (UIC codes)</li>
<li>Bypasses limitation of Gestpay which provides only one return url therefore allowing customer to return to the same website language where he/she has purchased. Mostly useful when you have set website code in your URLs</li>
<li>Absence of PHP's SOAP extension is logged instead of generating an exception</li>
</ul>

Other features being published soon.

Usage
<ul>
<li>Upload module to your Magento instance.</li>
<li>Refesh caches</li>
<li>Set your options in the Payment's section of the System Configuration</li>
<li>Set-up your Gestpay's panels with the following URLs: <br />
Reply URL (both positive and negative) http://your.shopdomain.com/magestpay/gestpay/result <br />
Server to server URL : http://your.shopdomain.com/magestpay/gestpay/s2s <br />
</ul>

