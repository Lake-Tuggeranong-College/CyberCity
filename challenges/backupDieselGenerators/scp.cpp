#include <iostream>
#include <thread>
#include <atomic>
#include <libssh/libssh.h>
#include <stdio.h>
#include <string>

/*
You can attempt to compile this for windows if you wish. I know i'm never going to attempt that again. I hate this challenge. To complete it open it in a
hex editor, the online one avaliable here: https://hexed.it/ is perfectly capable. Use the search feature and search for something like "mog" in order to 
find the default address, (123 Mogworth St, Cybercity). Replace each character accordingly (rewrite the address) to "456 Grimace Shake Rd, Ohio". This will
cause the address comparison function to work correctly and the program will download the correct PDF accordingly. Note that is named the same as the defualt
address one that does not contain the CTF{} flag. 

If you're dense enough that you can't live without changing the address, note that the two addresses should (haven't tested if it's true) have the same
number of characters. Something something memory addresses byte bit addresses something something.

- Ajay Sayer.
*/

// Check address and set remote file path accordingly
std::string address = "123 Mogworth St, CyberCity"; //Address
std::string address2 = "456 Grimace Shake Rd, Ohio"; //This is how i got it to work on linux. Live with it bruh.

std::atomic<bool> stopLoadingAnimation(false);

void printLoadingAnimation() {
    const char animation[] = "|/-\\";
    int animationIndex = 0;
    while (!stopLoadingAnimation) {
        std::cout << "\r" << "Please wait, connecting " << animation[animationIndex++] << std::flush;
        if (animationIndex == 4) animationIndex = 0;
        std::this_thread::sleep_for(std::chrono::milliseconds(100)); // Adjust the delay as needed
    }
}



int main() {
    // Display a message indicating that the terminal is open
    std::cout << "Terminal opened. Press Enter to establish an SSH connection..." << std::endl;

    // Wait for user input
    std::string userInput;
    std::getline(std::cin, userInput);

    // Start loading animation in a separate thread
    std::thread loadingThread(printLoadingAnimation);

    // SSH variables
    ssh_session sshSession;
    ssh_scp scpSession;
    const char *remoteFilePath; // Remote file path based on address
    const char *localFilePath = "./BWShippingInvoice.pdf"; // Default downloaded file path

    // Initialize SSH session
    sshSession = ssh_new();
    if (sshSession == nullptr) {
        std::cerr << "Failed to create SSH session" << std::endl;
        return 1;
    }

    // Set SSH options
    ssh_options_set(sshSession, SSH_OPTIONS_HOST, "10.177.200.71");
    ssh_options_set(sshSession, SSH_OPTIONS_USER, "ltc");

    // Connect to SSH server
    int rc = ssh_connect(sshSession);
    if (rc != SSH_OK) {
        std::cerr << "Failed to connect to SSH server: " << ssh_get_error(sshSession) << std::endl;
        ssh_free(sshSession);
        return 1;
    }

    stopLoadingAnimation = true; // Stop the loading animation
    loadingThread.join(); // Wait for the loading thread to finish

    // Authenticate using password
    rc = ssh_userauth_password(sshSession, nullptr, "LTCpcgame5");
    if (rc != SSH_AUTH_SUCCESS) {
        std::cerr << "Failed to authenticate: " << ssh_get_error(sshSession) << std::endl;
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    std::cout << "\r" << "Authenticated successfully! Downloading file..." << std::endl;

    /* these can be uncommented for debugging
    std::cout << address << std::endl;
    std::cout << address2 << std::endl;
    */

    if (address == "456 Grimace Shake Rd, Ohio") {
        remoteFilePath = "/var/www/CyberCity/serverFiles/backupDieselGenerators/BlueWhaleShippingCTF.pdf";
    } else {
        remoteFilePath = "/var/www/CyberCity/serverFiles/backupDieselGenerators/BlueWhaleShipping.pdf";
    }

    /* these can be uncommented for debugging
    if (address == address2) {
        std::cout << "it works" << std::endl;
    } else {
        std::cout << "it doesn't works" << std::endl;;
    }

    std::cout << address << std::endl; these can be uncommented for debugging
    std::cout << address2 << std::endl;
    */
    // Start SCP session for downloading
    scpSession = ssh_scp_new(sshSession, SSH_SCP_READ, remoteFilePath);
    if (scpSession == nullptr) {
        std::cerr << "Failed to create SCP session: " << ssh_get_error(sshSession) << std::endl;
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    // Initiate file transfer
    rc = ssh_scp_init(scpSession);
    if (rc != SSH_OK) {
        std::cerr << "SCP initialization failed: " << ssh_get_error(sshSession) << std::endl;
        ssh_scp_free(scpSession);
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    // Receive the file
    rc = ssh_scp_pull_request(scpSession);
    if (rc != SSH_SCP_REQUEST_NEWFILE) {
        std::cerr << "Failed to receive file: " << ssh_get_error(sshSession) << std::endl;
        ssh_scp_free(scpSession);
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    std::cout << "Opening local file to write" << std::endl;  

    // Open local file for writing
    FILE *localFile = fopen(localFilePath, "wb");
    if (localFile == nullptr) {
        std::cerr << "Failed to open local file for writing" << std::endl;
        ssh_scp_free(scpSession);
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    std::cout << "Reading data from SCP" << std::endl;  

    // Read data from SCP and write to local file
    char buffer[4096];
    size_t bytesRead;
    for (int i = 0; i < 50; ++i) {
         bytesRead = ssh_scp_read(scpSession, buffer, sizeof(buffer));
         //std::cout << "Read Data" << std::endl;  this can be uncommented for debugging
         if (bytesRead > 0) {
            fwrite(buffer, 1, bytesRead, localFile);
            //std::cout << "Write Data" << std::endl;  this can be uncommented for debugging
    } else {
        break; // Exit the loop if no more data to read
        }
    }


    std::cout << "File downloaded successfully" << std::endl;   

    // Close local file
    fclose(localFile);

    std::cout << "File downloaded successfully: " << localFilePath << std::endl;

    // Cleanup
    ssh_scp_close(scpSession);
    ssh_scp_free(scpSession);
    ssh_disconnect(sshSession);
    ssh_free(sshSession);

    // Ask the user for input
    std::cout << "Press enter to exit: ";
    // Get user input
    std::getline(std::cin, userInput);
    return 0;
}
