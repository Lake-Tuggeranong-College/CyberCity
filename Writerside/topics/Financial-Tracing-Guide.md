#  Financial Tracing Guide

![](image2.png)

First for this challenge you must download the Wireshark scenario

![](image3.png)

Once you have downloaded the Wireshark Scenario open it and then there is going to be about 2000 frames, so you must have a plan on how to find this frame

![](image4.png)

![](image5.png)

Most of the frames have a bunch of nonsense in them, so don't get distracted by it, a simple way to find it is to select a filter on icmp but if you don't know to sort by that you will be in a world of pain

![](image7.png)

Once sorted by icmp there's only 1 frame available
  
![](image6.png)

And that's how you get the flag `CTF{WaterTime}`

But if you don’t know how/or don’t think of searching for icmp then it will take a lot longer   

![](image5.png)

The select frame is #212/1953, just hope you don’t scroll over it.

![](image1.png)