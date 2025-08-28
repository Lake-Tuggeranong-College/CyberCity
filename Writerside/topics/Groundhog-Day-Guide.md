# Groundhog Day Guide

If you look at the first few letters in the description you will see the two things you need to decrypt this long string of text.

![groundhogdayGuide.png](groundhogdayGuide.png)

"B64" stands for Base64, a type of encryption that you need to use to find the flag. We recommend using CyberChef for decryption.

![groundhogdayGuide2.png](groundhogdayGuide2.png)

The issue is once you attempt decoding using Base64, it does print the result required.

![groundhogdayGuide3.png](groundhogdayGuide3.png)

You will notice if you look again at the challenge description you will see a number next to "B64", the 4 there indicates how many times you need to decrypt the text.

![groundhogdayGuide4.png](groundhogdayGuide4.png)

If you drag the "From Base64" module 4 times into the recipe box while keeping the original text the same you will see the answer. Copy the flag into the answer box and recieve your points.