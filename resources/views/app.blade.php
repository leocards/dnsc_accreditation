<!DOCTYPE html>
<html class="{{ Auth::user()->theme??'' }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="{{ asset('img/dnsc_logo.png') }}">
    @vite('resources/css/app.css')
    @inertiaHead
    @vite('resources/js/app.js')
  </head>
  <body class="dark:bg-primaryDarkBg">
    @inertia
    <div class="fixed -bottom-10 left-[10rem] -z-50 w-[12rem] h-screen bg-[#01a66f] rounded-full -rotate-45 opacity-5 dark:opacity-[0.02] blur-2xl pointer-events-none"></div>
    <div class="fixed -bottom-24 right-[50rem] -z-50 w-[12rem] h-screen bg-[#01a66f] rounded-full -rotate-45 opacity-5 dark:opacity-[0.02] blur-2xl pointer-events-none"></div>
    <div class="fixed -bottom-[20rem] right-[20rem] -z-50 w-[6rem] h-screen bg-white rounded-full -rotate-45 opacity-70 dark:opacity-[0.009] blur-xl pointer-events-none"></div>
    <div class="fixed -top-24 right-[20rem] -z-50 w-[5rem] h-screen bg-[#00aead] rounded-full -rotate-45 opacity-5 dark:opacity-[0.02] blur-2xl pointer-events-none"></div>
  </body>
</html>