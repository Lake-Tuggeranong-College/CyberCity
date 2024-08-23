# Intercepted Communication

The grep command is like a super-powered search tool for your terminal. Here’s how you can use it in simple terms:

Basic Search: Imagine you have a big text file and you want to find all the lines that mention “apple”. You can use grep to do this:

    grep "apple" filename.txt

This will show you all the lines in filename.txt that contain the word “apple”.

Case Insensitive Search: If you don’t care about uppercase or lowercase, you can add -i:

    grep -i "apple" filename.txt

This will find “apple”, “Apple”, “APPLE”, etc.

Search in Multiple Files: You can search in multiple files at once by using a wildcard *:

    grep "apple" *.txt

This will search for “apple” in all .txt files in the current directory.

Show Line Numbers: If you want to know where exactly the word appears, you can add -n:

    grep -n "apple" filename.txt

This will show the line numbers along with the lines that contain “apple”.

Search Recursively: If you want to search through all files in a directory and its subdirectories, use -r:

    grep -r "apple" /path/to/directory

This will search for “apple” in all files within the specified directory and its subdirectories.

Count Matches: If you just want to know how many times “apple” appears, use -c:

    grep -c "apple" filename.txt

This will give you the count of lines that contain “apple”.

Think of grep as a way to quickly find specific words or patterns in your files, making it easier to locate information without having to manually scroll through everything.