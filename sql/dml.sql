-- Insert 5 users (all users has its username as password)
INSERT INTO User (`alias`, `email`, `password`)
VALUES
  ("miim", "miim@example.com", "$2y$10$VNAxBgE08FMj9tSQkvTAp.4e.HI8b1og5vjSP8ssFqhfj78USfd56"),
  ("ratz", "ratz@example.com", "$2y$10$OpA3iFtgsoWTO92MjKFGjOEmDuMj0ikrUFLq2KU659Poa1vpE2Dm6"),
  ("const", "const@example.com", "$2y$10$M6xFemZ8GGDJFB12JjBZCeG8ZFJ.qGcpARRWRnAPbApAMLKtK3pKe"),
  ("super", "super@example.com", "$2y$10$Op/W/OCOxuGbr1usYkTU5uGHT0EfiVi5U1mngqYReVmue7wFYhaXy"),
  ("pragma", "pragma@example.com", "$2y$10$5prGTMkpFva1vSBwpmzs0.LRq4pftKFb4Cjt52eczBL2AMo2lk/ee"),
  ("codegeek", "codegeek@example.com", "$2y$10$d15RMEYjyt3kUxdpr1KFvuZwQCh562NL62VJYoZDiQ.F7dnMq5uui")
;

-- Insert 6 questions
-- TODO: Change created and add random updated
-- Date format: YYYY-MM-DD HH:MM:SS
INSERT INTO Question (`userId`, `title`, `created`, `updated`, `content`)
VALUES
  (1, "How do i write a loop in javascript?", "2020-01-04 02:47:34", NULL, "I cant seem to get a loop right i've tried like this:

```
loop (1 to 5 into x) {
  console.log(x);
}
```"),
  (1, "How can i get factorials in PHP?", "2020-01-06 11:30:32", NULL, "How can i get the factorial of for example 5 in PHP?
Im not very good with maths so i dont get how to implement it."),
  (2, "How can i randomly select a number between 1 and 10?", "2020-01-07 00:36:07", NULL, "Like the title says, i've tried looking through the docs but it's too boring."),
  (3, "Need help reading a file in Node", "2020-01-07 23:28:16", NULL, "I want to read a file (file.txt) from the folder where my main script is.
This doesn't seem to work:

```
var fs = require('fs');

var data = fs.readFile('file.txt');
```"),
  (4, "How can i send a GET request?", "2020-01-08 19:27:06", NULL, "I want to get data from an API, _https://someapi.com_, in my website and i don't want a backend server.
Can i get the data **after** loading the page?"),
  (2, "In PHP, how do i get the sum of an array?", "2020-01-09 13:44:34", NULL, "I know i can easily do this by a foreach function like this:

```
$nums = [ 3, 1, 7, 5, 8 ];
$sum = 0;

foreach ($nums as $num) {
  $sum += $num;
}

echo $sum; // prints 24
```

But is there a more efficient way?")
;

-- Insert some tags
INSERT INTO Tag (`tag`)
VALUES
  ("webdev"),
  ("backend"),
  ("frontend"),
  ("nodejs"),
  ("javascript"),
  ("python"),
  ("php")
;

-- Link tags to questions
INSERT INTO QuestionToTags (`questionId`, `tagId`)
VALUES
  (1, 1),
  (1, 5),
  (2, 1),
  (2, 7),
  (3, 1),
  (3, 3),
  (3, 6),
  (4, 1),
  (4, 2),
  (4, 4),
  (4, 5),
  (5, 1),
  (5, 3),
  (5, 4),
  (6, 1),
  (6, 2),
  (6, 7)
;


-- Add answers to questions
INSERT INTO Answer (`questionId`, `userId`, `created`, `updated`, `content`)
VALUES
  (1, 3, "2020-01-04 11:40:27", NULL, "That's **NOT** how you write a loop in Javascript!
You're gonna have to create an array and then loop it using .forEach();

```
let numbers = [1, 2, 3, 4, 5];

numbers.forEach(number => {
  console.log(number);
});
```"),
  (1, 5, "2020-01-04 22:23:57", NULL, "The best way to do this is using a **for** loop.

```
for (let n = 1; n <= 5; n++) {
  console.log(n);
}
```"),
  (2, 3, "2020-01-06 14:15:00", NULL, "If you have the [GMP extension](https://www.php.net/manual/en/book.gmp.php) available you can use the _gmp_fact_ function.

```
$fact = gmp_fact(5);
$result = gmp_intval($fact);
echo $result; // 120 (5 factorial)
```"),
  (2, 6, "2020-01-07 15:58:41", "2020-01-08 18:21:20", "This can be easily done with a recursive function.

```
function factorial(int $n): int
{
  return $n == 0 ? 1 : $n * factorial($n - 1);
}

$result = factorial(3);
echo $result; // prints 6
```"),
  (2, 4, "2020-01-07 22:35:09", NULL, "Like this:

```
function factorial($n) {
  $fact = 1;

  for ($i = 2; $i < $n; $i++) {
    $fact *= $i;
  }

  return $fact;
}

$result = factorial(8);
echo $result; // prints 5040
```"),
  (3, 5, "2020-01-09 16:11:46", "20-01-12 00:25:13", "You need to import the **random** package and use `random.randint()`.

```
import random

num = random.randint(1, 10)
```"),
  (4, 5, "2020-01-09 21:34:31", NULL, "You almost had it, however you can read a file _synchronously_ or _asynchronously_.
With _synchronously_ meaning that the program will halt while reading the file, while the latter means the opposite.
But by the look of your code you seem to want the first option.

Synchronously:

```
const fs = require('fs');
let data = fs.readFileSync('file.txt', utf8');

console.log(data); // prints the files contents
```

Asynchronously:

```
const fs = require('fs');

fs.readFile('file.txt', 'utf8', (ex, data) => {
  if (ex) {
    throw ex;
  }

  console.log(data); // prints the files contents
});
```"),
  (5, 2, "2020-01-10 20:34:10", "2020-01-13 16:52:28", "Here you go:

```
function getData(url, callback) {
  xhr.open('GET', url, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState != 4) return;

    if (xhr.status >= 200 && xhr.status < 300) {
      var data = JSON.parse(xhr.responseText);

      callback(null, data);
    } else {
      var ex = new Error(xhr.statusText);
      ex.status = xhr.status;

      callback(ex, null);
    }
  };

  xhr.send();
}

getData('https://someapi.com', function (ex, data) {
  if (ex) {
    throw ex;
  }

  console.log(data);
});
```"),
  (5, 5, "2020-01-11 00:12:15", "2020-01-11 00:14:18", "Using the quite recent `fetch` function this is really simple.
Assuming the api uses json as response body:

```
fetch('https://someapi.com')
  .then(res => res.json()) // parse the response body as json
  .then(data => {
    // Do something with data here
    console.log(data);
  })
  .catch(console.error);
```"),
  (6, 5, "2020-01-11 13:03:50", NULL, "Use the `array_sum` function.

```
$numbers = [ 3, 1, 7, 5, 8 ];
$sum = array_sum($numbers);

echo $sum; // prints 24
```"),
  (6, 3, "2020-01-11 19:06:14", NULL, "I prefer using **array_reduce**, gives me more control.

```
$numbers = [ 3, 1, 7, 5, 8 ];

$sum = array_reduce($numbers, function ($sum, $n) {
  return $sum + $n;
}, 0);
```")
;

-- Make some answers as the accepted answer.
UPDATE Question SET answerId = 2 WHERE id = 1;
UPDATE Question SET answerId = 4 WHERE id = 2;
UPDATE Question SET answerId = 8 WHERE id = 5;


-- Add commments to some questions
INSERT INTO Comment (`questionId`, `userId`, `created`, `updated`, `content`)
VALUES
  -- Question 3 Author 2
  (3, 1, "2020-01-08 08:07:34", "2020-01-10 22:05:29", "Don't be lazy, read the docs [random.randint](https://docs.python.org/3.7/library/random.html#random.randint)"),
  -- Question 4 Author 3
  (4, 5, "2020-01-09 12:55:12", "2020-01-11 03:18:35", "You almost have it right, you need to add _Sync_ at the end of the function name."),
  -- Question 4 Author 3
  (4, 1, "2020-01-10 07:26:30", NULL, "Just read the docs, [fs.readFileSync](https://nodejs.org/api/fs.html#fs_fs_readfilesync_path_options)."),
  -- Question 5 Author 4
  (5, 2, "2020-01-11 10:08:14", "2020-01-14 12:28:22", "Read [this](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch) article on MDN."),
  -- Question 6 Author 2
  (6, 3, "2020-01-12 13:11:58", NULL, "There is nothing wrong with your approach, you could use [array_sum](https://www.php.net/manual/en/function.array-sum.php) if you'd like to though.")
;

-- Add comments to some answers
INSERT INTO Comment (`questionId`, `answerId`, `userId`, `created`, `updated`, `content`)
VALUES
  -- Question 1 Answer 1 Author 3
  (1, 1, 2, "2020-01-07 23:24:50", NULL, "Seems a bit redundant, a regular loop is strongly recommended."),
  (1, 1, 1, "2020-01-09 16:39:11", NULL, "Yeah creating an array just for looping seems to be a bad way to just loop a range."),
  -- Question 1 Answer 2 Author 5
  -- Question 2 Answer 3 Author 3
  -- Question 2 Answer 4 Author 6
  (2, 4, 3, "2020-01-09 17:42:52", "2020-01-15 21:24:36", "This will fail if the call stack is exceeded. But should work for most cases."),
  -- Question 2 Answer 5 Author 4
  -- Question 3 Answer 6 Author 5
  -- Question 4 Answer 7 Author 5
  (4, 7, 6, "2020-01-11 18:34:46", NULL, "Keep in mind that all relative files are read from _current_working_directory_.
So if the program is started from another directory this will fail.

To fix this you can prepend `__dirname` to the file like `fs.readFileSync(__dirname + '/file.txt', 'utf8');`.
"),
  -- Question 5 Answer 8 Author 2
  (5, 8, 6, "2020-01-12 20:20:29", NULL, "Using XHR in 2020 is really only required if you want to support IE. Fetch works great most of the time."),
  (5, 8, 3, "2020-01-14 08:41:19", "2020-01-14 10:17:21", "If the API uses JSON as response then do `var json = JSON.parse(data);` to parse it to a native object."),
  -- Question 5 Answer 9 Author 5
  (5, 9, 3, "2020-01-14 21:18:54", "2020-01-14 23:28:53", "Can inform that to get the data as text replace `res.json()` with `res.text()`."),
  -- Question 6 Answer 10 Author 5
  (6, 10, 2, "2020-01-15 13:22:17", NULL, "This was exactly what i was looking for, thanks!"),
  -- Question 6 Answer 11 Author 3
  (6, 11, 5, "2020-01-16 16:31:36", "2020-01-15 23:31:36", "Doing this seems overkill, using array_sum is the correct way, check my answer.")
;

-- Vote on questions
INSERT INTO Vote (`questionId`, `userId`, `vote`)
VALUES
  -- Question 1 Author 1
  (1, 3, -1),
  (1, 6, 1),
  -- Question 2 Author 1
  (2, 4, 1),
  -- Question 3 Author 2
  (3, 1, -1),
  (3, 5, -1),
  (3, 4, -1),
  (3, 3, 1),
  -- Question 4 Author 3
  (4, 1, 1),
  (4, 4, 1),
  (4, 2, 1),
  (4, 6, -1),
  -- Question 5 Author 4
  (5, 1, 1),
  (5, 6, 1),
  (5, 5, 1),
  (5, 3, 1),
  (5, 2, 1),
  -- Question 6 Author 2
  (6, 5, -1),
  (6, 6, 1),
  (6, 1, 1)
;

-- Vote on answers
INSERT INTO Vote (`answerId`, `userId`, `vote`)
VALUES
  -- Question 1 Answer 1 Author 3
  (1, 2, -1),
  (1, 5, -1),
  (1, 6, -1),
  -- Question 1 Answer 2 Author 5
  (2, 2, 1),
  (2, 4, 1),
  (2, 1, 1),
  (2, 3, 1),
  -- Question 2 Answer 3 Author 3
  (3, 5, 1),
  (3, 4, 1),
  (3, 2, -1),
  -- Question 2 Answer 4 Author 6
  (4, 4, -1),
  (4, 2, 1),
  (4, 1, 1),
  (4, 5, 1),
  (4, 3, 1),
  -- Question 2 Answer 5 Author 4
  (5, 3, 1),
  (5, 5, 1),
  (5, 2, 1),
  -- Question 3 Answer 6 Author 5
  (6, 3, 1),
  (6, 4, 1),
  (6, 6, 1),
  (6, 1, 1),
  -- Question 4 Answer 7 Author 5
  (7, 1, 1),
  (7, 4, 1),
  (7, 3, 1),
  -- Question 5 Answer 8 Author 2
  (8, 1, 1),
  (8, 4, 1),
  -- Question 5 Answer 9 Author 5
  (9, 3, 1),
  (9, 6, 1),
  (9, 1, 1),
  -- Question 6 Answer 10 Author 5
  (10, 3, 1),
  (10, 4, 1),
  (10, 6, 1),
  -- Question 6 Answer 11 Author 3
  (11, 6, -1),
  (11, 4, 1)
;

-- Vote on comments
INSERT INTO Vote (`commentId`, `userId`, `vote`)
VALUES
  -- Comment  1 Question 3 Author 1
  (1, 3, 1),
  (1, 5, 1),
  -- Comment  2 Question 4 Author 5
  (2, 4, 1),
  -- Comment  3 Question 4 Author 1
  (3, 4, 1),
  (3, 6, 1),
  -- Comment  4 Question 5 Author 2
  (4, 6, 1),
  (4, 1, 1),
  (4, 5, 1),
  -- Comment  5 Question 6 Author 3
  (5, 5, 1),
  -- Comment  6 Answer 1 Author 2
  (6, 4, 1),
  (6, 6, 1),
  -- Comment  7 Answer 1 Author 1
  -- Comment  8 Answer 4 Author 3
  (8, 6, 1),
  (8, 5, 1),
  (8, 2, 1),
  -- Comment  9 Answer 7 Author 6
  (9, 3, 1),
  (9, 4, 1),
  -- Comment 10 Answer 8 Author 6
  (10, 5, 1),
  -- Comment 11 Answer 8 Author 3
  (11, 0, 1),
  -- Comment 12 Answer 9 Author 3
  (12, 2, 1),
  (12, 4, 1),
  -- Comment 13 Answer 10 Author 2
  -- Comment 14 Answer 11 Author 5
  (14, 4, -1)
;
