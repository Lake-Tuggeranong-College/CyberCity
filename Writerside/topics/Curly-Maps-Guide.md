# Curly Maps Guide

1. Open Terminal
2. Use the 'ssh' command and write this using the information in the description: *username*@*IP* -p *port*
3. Use 'ifconfig'
4. With this information, find the IP address (inet) under 'eth1'
5. Use the 'nmap' command with the IP you found under 'eth1'. Make sure to add '/24' at the end of your IP.
6. Once you do that you can find another IP, then use the curl command with that IP.