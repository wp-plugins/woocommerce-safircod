=== WooCommerce SafirCOD ===
Contributors: Domanjiri
Tags: safir,cod, woocommerce, shop, safircod, post, cash on delivery, iran, iranian, persian, woo commerce, ecommerce, e-commerce, shipping, farsi
Requires at least: 3.6
Tested up to: 3.8
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates SafirCOD, an Iranian Cash on Delivery service, with WooCommerce.

== Description ==

= EN: =

Get shipping cost from SafirCOD, an Iranian Cash on Delivery service which support shipping over country, and then register shop order in this system. 

(Pesian description)
= FA: =

این افزونه فروشگاه [ووکامرسی](http://woocommerce.ir) شما را به سیستم **سفیر** وصل می‌کند. با نصب افزونه، دو روش ارسال *پست پیشتاز* و *پست سفارشی* به فروشگاه شما اضافه خواهد شد.

در زمان ثبت سفارش توسط خریدار، ابتدا موقعیت خریدار دریافت می‌گردد (استان و شهر) و سپس هزینه‌ی ارسال کالا به دو روش پیشتاز و سفارشی از سیستم سفیر استعلام شده به او نمایش داده خواهد شد. در صورت انتخاب هر یک از این دو روش، پس از تکمیل شدن فرم مشخصات، سفارش به طور خودکار در پنل شرکت سفیر ثبت می‌گردد.

افزونه، هر چند ساعت یکبار وضعیت سفارش‌ها را از سفیر استعلام کرده و اطلاعات فروشگاه را بروز می‌کند، در نتیجه خریدار با کد رهگیری‌ای که در انتهای خرید خود دریافت کرده، *در محیط فروشگاه شما*، می‌تواند وضعیت سفارش خود را پیگیری کند.


= در این افزونه =

* 
انتخاب استان و شهر به *سبد خرید* منتقل شد تا کاربر در ابتدای خرید خود، بتواند هزینه‌ی ارسال کالا را مشاهده کند. 
* 
فیلدهای *کشور، *استان* و *شهر* از فرم خرید (یا همان فرم دریافت آدرس) حذف شدند.
* 
هزینه‌های ارسال کالا که از سیستم **سفیر** استعلام می‌شوند به مدت محدودی در دیتابیس ذخیره می‌گردد تا در صورت تکرار خرید، و یا انجام خرید مشابه، نیازی به استعلام مجدد نباشد.
* 
در صورتی که به هر دلیلی، وب سرویس شرکت سفیر امکان پاسخ‌گویی نداشته باشد، سفارش با *موفقیت* در سایت ثبت می‌شود. در این مواقع مدیر سایت توسط ایمیلی آگاه می‌شود تا نسبت به ثبت دستی سفارش در پنل سفیر اقدام کند.
* 
هر 24 ساعت یکبار وضعیت سفارش‌های تکمیل نشده، از سیستم سفیر استعلام و بروزرسانی می‌شود. بنابراین خریدار با وارد کردن کد رهگیری در ابزارک رهگیری ووکامرس، از آخرین وضعیت سفارش آگاه می‌گردد.


**نصب**

برای دانستن چگونگی نصب افزونه، سربرگ [نصب](http://wordpress.org/extend/plugins/woocommerce-safircod/installation/) را مطالعه کنید. همچنین فایل راهنمای نصب با فرمت پی-دی-اف ، در بسته‌ی دانلودی در دسترس است، در این فایل مراحل تنظیم و راه‌اندازی افزونه به کمک تصاویری توضیح داده شده است.


**Requirements:**

1. An installed version of [Persian WooCommerce](http://woocommerce.ir/ "Safir COD")
2. An active account on [Safir](http://safircod.com/ "Safir COD")


== Installation ==

= EN: =
1. Upload the entire `woocommerce-safircod` folder to the `/wp-content/plugins/` directory -- or just upload the ZIP package via 'Plugins > Add New > Upload' in your WP Admin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the WooCommerce 'Setting' menu, set currency to 'Rial' or 'Toman'
4. Again in the WooCommerce 'Setting' menu, in 'Shipping' sub-menu, insert your Safir account data in the field's of 'Pishtaz' method
5. In the 'Gateway' sub-menu, restrict cod method to 'pishtaz' and 'sefareshi' 

= FA: =

1. پوشه‌ی اصلی را بطور کامل در پوشه‌ی افزونه‌های خود آپلود کنید، یا اینکه فایل زیپ را با از مسیر 'افزونه‌ها > افزودن > بارگزاری'  نصب کنید
2. از منوی 'افزونه‌ها' ، افزونه‌ی سفیر را فعال کنید. دقت کنید که افزونه‌ی *ووکامرس* باید پیش از این نصب و فعال شده باشد
3. به تنظیمات ووکامرس بروید و واحد پولی را روی *ریال* یا *تومان* قرار دهید
4. در تنظیمات ووکامرس، به قسمت *حمل و نقل* بروید،  روی لینک *پست پیشتاز*  کلیک کنید و اطلاعات حساب سفیر خود را وارد نمایید
5. در تنظیمات، به قسمت *درگاه‌های پرداخت* رفته و *پرداخت هنگام تحویل* را کلیک کنید. در قسمت *فعال کردن روش‌های ارسال* دو گزینه‌ی پست سفارشی و پست پیشتاز را انتخاب کنید

== Frequently Asked Questions ==

= where i can find more information about this plugin and about Safir? =
Visit  [safircod ](http://safircod.com) for more information.

== Screenshots ==

1. shipping method
2. setting
3. calc shipping cost in cart

== Changelog ==
= 1.2 =
compatible with with new version of woocommerce. best performance with woocommerce 1.2.x and upper.

= 1.1 =
compatible with with new version of safir webservice

= 1.0 =
* Just launch..

== Upgrade Notice ==

== Donations ==