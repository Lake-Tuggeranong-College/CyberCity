#include <iostream>
#include <thread>
#include <atomic>
#include <libssh/libssh.h>
#include <stdio.h>


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

int getAddressStatus(const std::string& address) {
    if (address == "123 Mogworth St, CyberCity") {
        return 1;
    } else {
        return 0;
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



    // Check address and set remote file path accordingly
    std::string address = "123 Mogworth St, CyberCity"; // Example address


    int addressStatus = getAddressStatus(address);
    if (addressStatus == 1) {
        remoteFilePath = "/var/www/CyberCity/Challenge_Files/backupDieselGenerators/BlueWhaleShipping.pdf";
    } else {
        remoteFilePath = "/var/www/CyberCity/Challenge_Files/backupDieselGenerators/BlueWhaleShippingCTF.pdf";
    }

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
         std::cout << "Read Data" << std::endl;  
         if (bytesRead > 0) {
            fwrite(buffer, 1, bytesRead, localFile);
            std::cout << "Write Data" << std::endl;  
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
