---
title: 4 PHP Shorthand Operators
description: Unlock the Power of PHP 8 Shorthand Operators. Dive into four game-changing shorthand operators that make PHP development smoother than ever. 
category: Guides & Tutorials
tags: [PHP]
---

## Introduction

Ever take a moment to appreciate how far PHP has come? With each new version, PHP has grown into a mature language, offering us a bouquet of tools that make our coding journey smoother and more enjoyable. From battling those pesky null values using `isset`, `empty` and whatnot to embracing the elegance of modern PHP – we've come a long way. And today, I want to take you on a journey through some of the shorthand operators that we've picked along the way.

In this article, we're going to explore four PHP 8 shorthand operators that are changing the game. They've allowed us to write shorter and more elegant code.

Here's an overview of the Shorthand Operators we'll be exploring, all available as of PHP 8. Feel free to navigate directly to the one that piques your interest:
1.	[The Ternary Operator](#the-ternary-operator)
2.	[The Null Coalescing Operator](#the-null-coalescing-operator)
3.	[The Spaceship Operator](#the-spaceship-operator)
4.	[The Null Safe Operator](#the-null-safe-operator)
      
## The Ternary Operator

The ternary operator is a time-saving shortcut for the traditional `if…else` statement. You might have seen it in action in other programming languages too, where it carries out the same function. This operator is not necessarily one of the new cats, since it’s been around since the PHP 5.3 days, but I felt it’s worthy of mention.
Consider this scenario: You have a condition that decides whether something is 'name' or 'age'. Instead of crafting the usual if...else block like this:

```php
if (condition) {
  $result = ‘name’;
} else {
  $result = ‘age’;
}
```

You can slice through the clutter and use the ternary operator:

```php
$result = condition ? ‘name’ : ‘age’;
```

In this snippet, PHP evaluates the condition statement. If it comes up as true, the left-hand operand gets assigned to $result; if false, the right-hand operand takes the throne.

> The name ‘ternary’ comes from the fact that the expression requires three operands to be complete – The condition, the result statement for true, and the result statement for false

The condition can be pretty much anything—strings, integers, objects, arrays, Booleans, or expressions. Just a heads-up, if the condition is falsey (meaning it evaluates to false), the right-hand side gets the spotlight You can check out the bool column in the first table on this [PHP Manual page](https://www.php.net/manual/en/types.comparisons.php) for a handy guide. Generally, values like `0`, `""`, `[]`, `'0'`, `null`, `undefined`, and of course, `false` itself, all fall into the falsey category.

### Chaining Ternary Operators

Chaining of ternary operators is a little bit tricky and is not usually advisable unless on extreme occasions. Let’s consider the snippet below:

```php
$user->delivered_at = now();
$user->paid_at = null;
$status = $user->delivered_at ? 'Order Delivered'
                : $user->paid_at ? 'Payment Done' : 'Awaiting Payment';
echo $status;
```
Chaining ternary operators like this is unpredictable. Unlike in languages like C and C++ where the ternary operator is right-associative, the ternary operator in PHP is left-associative. You’d expect this snippet to print ‘Order Delivered’ but surprisingly, it is going to return ‘Awaiting Payment’ instead. The $user->paid_at condition will always be evaluated first, and the value returned even before it gets to the second condition.

Therefore, to make sure your code behaves as intended, you have to use parenthesis in your expression just like this

```php
$user->delivered_at = time();
$user->paid_at = null;
$status = $user->delivered_at ? 'Order Delivered'
         : ($user->paid_at ? 'Payment Done' : 'Awaiting Payment');
echo $status;
```
As at PHP 7.4, the use of chained ternaries without the parenthesis is deprecated. Most times, you are advised to use the `if...else` or `if…elseif` block instead of chaining ternaries as they are not so readable. Sometimes, it's better to sacrifice a few lines of code for readability 🤷🏽‍♂️

## The Shorthand Ternary Operator

As from PHP 5.3, this statement

```php
$type = $initial ? $initial : 'writer';
```
can further be shortened as follows:

```php
$type = $initial ?: 'writer';
```
In the above, the expression in the `$initial` is evaluated as `boolean` and if `true`, its value is assigned to `$type`. If falsey, however, the value `'writer'` will be assigned instead. It is shorter than the usual ternary operator and is technically no longer ternary (if you want to consider the three-operator rule).

## The Null Coalescing Operator

The Null Coalescing Operator was introduced in PHP 7.0. It behaves almost like the ternary operator. The difference is that it evaluates the left operand like `isset()` unlike the ternary operator that evaluates the boolean value. This makes this operator handy when checking for array key existence and assigning default values for expressions.

The Null operator takes two operands and decides which to use based on the value of the left operand.  Since it works like isset(), values like `0`, empty string (“”), empty array(`[]`), and false all return true since they have been assigned. Only null and undefined will return false in the null coalescing operator.

```php
$age = null;
$age ?? 10; // 10

$age = 20;
$age ?? 10; // 20

'' ?? 10; // ''
30 ?? 10; // 10
'0' ?? 10; // '0'
0 ?? 10; // 0
false ?? 10; // false
```

Let’s take a realistic example of working with forms when you want to check if a value exists in the $_POST superglobal array and assign a default value ,
You may try to use the ternary operator as such,

```php
$size = $_POST['size'] ? $_POST['size']: 10;
```

However, the above statement will throw an error if the size key doesn't exist in the array. This is because it tries to access the value first and check its boolean value, and since the array key does not exist, it becomes a problem. A quick fix in previous versions of PHP will be to wrap it with the isset() function like this

```php
$size = isset($_POST['size']) ? $_POST['size']: 10;
```

This statement assigned the `$_POST['size']` to the $size variable if the key size exists in the array and is not null else, `10` is assigned.

The Null Coalescing Operator makes it even shorter and more convenient for us

```php
$size = $_POST['size'] ?? 10;
```
Sweet right?? Absolutely.

The Null Coalescing operator also works on nested objects even if the property is null

```php
$user = (object) [
    'name' => 'Kyrian Obikwelu',
    'age' => 22
];

echo ($user->country->name ?? 'No country assigned');

// OUTPUT: No country assigned
```


### Chaining in Null Coalescing Operator

PHP allows you to chain multiple null coalescing operators like this

```php
$size = $_POST['size'] ?? $default_size ?? 10;
```

In the statement above, if the `$_POST['size']` and `$default_size` do not exist or are null, then `10` will be assigned to the variable `$size`.

## Null Coalescing Assignment Operator

Just like the shorthand ternary operator, the null coalescing assignment operator can be used to shorten the null coalescing operator when it’s used for assignment.

For example, the statement below,

```php
$size = $size ?? 10;
```
can be further shortened and simplified to

```php
$size ??= 10;
```

The operator assigns the right operand to the left if the left operand is null else it does nothing.

## The Spaceship Operator

The spaceship Operator made its debut in PHP 7.0 and is very useful. It is a three-way comparison operator (less than, equal to, and greater than) used to compare two expressions. It returns -1, 0, or 1 when the first expression is less than, equal to, or greater than the second expression respectively. This operator can be used with integers, floats, strings, arrays, objects, etc.

```php
10 <==> 20  // returns -1 as 10 is less than 20
20 <==> 10  // returns 1 as 20 is greater than 20
10 <==> 10  // returns 0 as they are both equal
```

It is important to note that the left operand is compared against the right and not otherwise. The order is very important in the spaceship operator. When comparing objects, the spaceship compares them using their properties and values.

For example:

```php
$date1 = now()->addDay();
$date2 = now()->addDays(2);

echo $date1 <=> $date2; // returns -1
```

The spaceship operator is commonly used for sorting elements in an array. The PHP `usort()` function allows a callback function that defines how the sort is applied.

```php
$data = [4, 5, 8, 2];

usort($data, function ($a, $b) {
    return $a <=> $b;
});

// [2, 4, 5, 8];
```

## The Null Safe Operator

This is a star operator, especially for those of us who've tangled with the Null Coalescing Operator's limitations when dealing with method calls. While the Null Coalescing Operator works wonders for array keys and object properties, it takes a step back when it comes to methods. It just wasn't designed for that role, leaving us to resort to tedious intermediate checks—a recipe for longer, less elegant code (who’s a fan of that, really?).

To put things in perspective, take a look at this snippet:

```php

class User
{
    public function getAddress(): ?Address { }
}

class Address
{
    public function getCountry(): ?Country { }
}

class Country
{
    public string $code;
}

$code = null;

if($user !== null)
{
    $address = $user->getAddress();
    if($address !== null) {
        $country = $address->getCountry();

        if($country !== null) {
            $code = $country->code;
        }
    }
}
```

Now, that's quite a journey, isn't it? Multiple checks upon checks—a fortress we had to build to fend off those dreaded "Call to a member function … on null" errors in the pre-PHP 8 era.

However, in PHP 8, the Null Safe Operator was introduced. The null-safe operator allows PHP to safely read the value of property and method return value. It short-circuits the retrieval if the value is null, without causing any errors. PHP starts from the left and looks at the left operand, if it’s null, it’ll simply discard whatever remains on the right and returns null. If the value is not null, however, it continues to the next property or method.

And here comes the icing on the cake—a stunning one-liner solution:l

```php
$code = $user?->getAddress()?->getCountry()?->code;
```

 The getCountry() method will only be called if the getAddress() method call does not return null, and the code property will be retrieved only if the getCountry() method returns a value not null else, null is returned.

But hold up, there's a fine print: the Null Safe Operator is all about reading data from objects. Writing data? Nope, not its scene.

## Conclusion

There you have it, the shorthand Comparison Operators as at PHP 8. Thanks for reading to the end. Happy Coding!!
