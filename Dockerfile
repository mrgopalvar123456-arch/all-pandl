FROM php:8.2-apache

# প্রজেক্টের সব ফাইল Apache সার্ভারের পাবলিক ডিরেক্টরিতে কপি করা হচ্ছে
COPY . /var/www/html/

# ডিরেক্টরির মালিকানা এবং পারমিশন Apache ইউজার (www-data)-কে দেওয়া হচ্ছে
RUN chown -R www-data:www-data /var/www/html

# Apache সার্ভারের Rewrite মডিউল চালু করা হচ্ছে
RUN a2enmod rewrite

# স্ট্যান্ডার্ড ওয়েব পোর্ট ৮০ ওপেন করা হচ্ছে
EXPOSE 80