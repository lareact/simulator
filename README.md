# 优雅的PHP爬虫扩展

## 使用
```
composer require z-golly/simulator
```

## 案例
```php
use Golly\Simulator\WebDriver;
use Golly\Simulator\Browser;

class Test{
    public function visit()
    {
        $browser = new Browser();
       
        $browser = $browser
            ->implicitlyWait(30)
            ->visit('https://www.amazon.com/dp/1231?language=en_US', function ($browser) {
                 $title = $browser->text('#productTitle');
                 $browser->clickLink('See all reviews');
            });      
    }
}
```
