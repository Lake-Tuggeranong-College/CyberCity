#include <stdlib.h>
#include <cstdio>
#include <unistd.h>

int main(int argc, char *argv[])
{
    // Check if an argument was provided
    if (argc < 2) {
        fprintf(stderr, "Usage: ./executor \"COMMAND\" \n" );
        return 1;
    }

    // Set UID to root
    setuid(0);

    // Build the command string with the argument
    char command[256];
    snprintf(command, sizeof(command), "/root/executor.sh %s", argv[1]);

    // Execute the command
    system(command);

    return 0;
}
