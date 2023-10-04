<p align="center"><img src="https://pec.ir/uploads/assets/images/pec-logo-new.png"></p>



#  درگاه پرداخت پارسیان برای لاراول | Laravel Parsian Payment Gateway

Parsian Library for laravel

کتابخانه درگاه پرداخت پارسیان برای لاراول

## روش نصب - Installation

Use composer to install this package

برای نصب و استفاده از این پکیج می توانید از کمپوسر استفاده کنید

```bash
composer require codeino/pec
```

## تنظیمات - Configuration

Add your pin to .env file

شناسه پذیرنده را در فایل .env اضافه کنید

```dotenv
PEC_PIN=XXXXXXXXXXXXXX
```

You can also define pin at runtime.

امکان تعیین کد پذیرنده در حین اجرا نیز وجود دارد که در ادامه توضیح داده خواهد شد.

To change currency to Toman (Default) 

برای تغییر واحد پول به تومان (مقدار پیشفرض)

```dotenv
PEC_CURRENCY=IRT
```

To change currency to Rial

برای تغییر واحد پول به ریال

```dotenv
PEC_CURRENCY=IRR
```

## روش استفاده | How to use

### ارسال مشتری به درگاه پرداخت | Send customer to payment gateway

```php
$response = pec_gateway()
    ->pin('XXXXXXXXXXXXXXX') // تعیین کد پذیرنده در حین اجرا - اختیاری
    ->amount(100) // مبلغ تراکنش
    ->request()
    ->callbackUrl('https://domain.com/verification') // آدرس برگشت پس از پرداخت
    ->mobile('09123456789') // شماره موبایل مشتری - اختیاری
    ->send();

if (!$response->success()) {
    return $response->error()->message();
}

// ذخیره اطلاعات در دیتابیس
// $response->authority();

// هدایت مشتری به درگاه پرداخت
return $response->redirect();
```

### بررسی وضعیت تراکنش | Verify payment status

```php
$authority = request()->query('Authority'); 
$status = request()->query('Status');

$response = pec_gateway()
    ->pin('XXXXXXXXXXXXXXX') // تعیین کد پذیرنده در حین اجرا - اختیاری
    ->amount(100)
    ->verification()
    ->authority($authority)
    ->send();

if (!$response->success()) {
    return $response->error()->message();
}

// دریافت هش شماره کارتی که مشتری برای پرداخت استفاده کرده است
// $response->cardHash();

// دریافت شماره کارتی که مشتری برای پرداخت استفاده کرده است (بصورت ماسک شده)
// $response->cardPan();

// پرداخت موفقیت آمیز بود
// دریافت شماره پیگیری تراکنش و انجام امور مربوط به دیتابیس
return $response->referenceId();
```
