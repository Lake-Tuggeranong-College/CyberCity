#include <stdlib.h>
#include <cstdio>
#include <unistd.h>

int main(int argc, char *argv[])
{
    // Check if an argument was provided
    if (argc < 2) {
        fprintf(stderr, "Usage: ./executor \"COMMAND\" \n");
        return 1;
    }

    // Set UID to root
    setuid(0);

    // Execute the command directly from the argument
    int status = system(argv[1]);
    
    // Check if the command execution was successful
    if (status == -1) {
        perror("system");
        return 1;
    }

    return 0;
}
