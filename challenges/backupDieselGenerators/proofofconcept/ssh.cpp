#include <iostream>
#include <thread>
#include <atomic>
#include <libssh/libssh.h>
#include <vector> // Include the vector header
#include <string> // Include the string header

#ifdef _WIN32
#include <conio.h>
#include <io.h>
#define read _read
#else
#include <unistd.h>
#endif

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

int authenticateByPassword(ssh_session session) {
    const char *password = "LTCpcgame5";
    int rc = ssh_userauth_password(session, NULL, password);
    if (rc != SSH_AUTH_SUCCESS) {
        std::cerr << "Authentication failed: " << ssh_get_error(session) << std::endl;
        return SSH_AUTH_ERROR;
    }
    return SSH_AUTH_SUCCESS;
}

int openShellSession(ssh_session session) {
    ssh_channel channel = ssh_channel_new(session);
    if (channel == NULL) {
        std::cerr << "Failed to create SSH channel" << std::endl;
        return SSH_ERROR;
    }

    int rc = ssh_channel_open_session(channel);
    if (rc != SSH_OK) {
        std::cerr << "Failed to open SSH session: " << ssh_get_error(session) << std::endl;
        ssh_channel_free(channel);
        return rc;
    }

    rc = ssh_channel_request_pty(channel);
    if (rc != SSH_OK) {
        std::cerr << "Failed to request PTY: " << ssh_get_error(session) << std::endl;
        ssh_channel_close(channel);
        ssh_channel_free(channel);
        return rc;
    }

    rc = ssh_channel_change_pty_size(channel, 80, 24);
    if (rc != SSH_OK) {
        std::cerr << "Failed to set PTY size: " << ssh_get_error(session) << std::endl;
        ssh_channel_close(channel);
        ssh_channel_free(channel);
        return rc;
    }

    rc = ssh_channel_request_shell(channel);
    if (rc != SSH_OK) {
        std::cerr << "Failed to request shell: " << ssh_get_error(session) << std::endl;
        ssh_channel_close(channel);
        ssh_channel_free(channel);
        return rc;
    }

    std::cout << "SSH shell session opened successfully!" << std::endl;

    // Define a list of commands to execute
    std::vector<std::string> commands = {
        "ls",
        "ifconfig"
    };

    // Execute each command in the list
    for (const auto& command : commands) {
        ssh_channel_write(channel, command.c_str(), command.length());
        ssh_channel_write(channel, "\n", 1); // Send a newline character to execute the command
    }

    // Read and print output from shell session
    char buffer[1024];
    int nbytes;
    fd_set fds;
    while (true) {
        FD_ZERO(&fds);
        FD_SET(0, &fds);
        FD_SET(ssh_get_fd(session), &fds);
        select(ssh_get_fd(session) + 1, &fds, NULL, NULL, NULL);

        if (FD_ISSET(0, &fds)) { // User input
            nbytes = read(0, buffer, sizeof(buffer));
            if (nbytes > 0) {
                buffer[nbytes] = '\0'; // Null-terminate the input buffer
                ssh_channel_write(channel, buffer, nbytes);
            }
        }
        if (FD_ISSET(ssh_get_fd(session), &fds)) { // Data from SSH server
            nbytes = ssh_channel_read(channel, buffer, sizeof(buffer), 0);
            if (nbytes > 0) {
                std::cout.write(buffer, nbytes);
            } else {
                break; // Channel closed
            }
        }
    }

    ssh_channel_close(channel);
    ssh_channel_free(channel);
    return SSH_OK;
}

int main() {
    // Display a message indicating that the terminal is open
    std::cout << "Terminal opened. Press Enter to establish an SSH connection..." << std::endl;

    // Wait for user input
    std::string userInput;
    std::getline(std::cin, userInput);

    // Start loading animation in a separate thread
    std::thread loadingThread(printLoadingAnimation);

    // Attempt to establish an SSH connection
    ssh_session sshSession = ssh_new();
    if (sshSession == nullptr) {
        std::cerr << "Failed to create SSH session" << std::endl;
        return 1;
    }

    ssh_options_set(sshSession, SSH_OPTIONS_HOST, "10.177.200.71");
    ssh_options_set(sshSession, SSH_OPTIONS_USER, "ltc");

    int rc = ssh_connect(sshSession);
    stopLoadingAnimation = true; // Stop the loading animation
    loadingThread.join(); // Wait for the loading thread to finish

    if (rc != SSH_OK) {
        std::cerr << "\r" << "Failed to connect: " << ssh_get_error(sshSession) << std::endl;
        std::cout << "Connection failed. Press Enter to retry or exit." << std::endl;
        std::getline(std::cin, userInput); // Wait for user to press Enter
        ssh_free(sshSession);
        return 1;
    }

    std::cout << "\r" << "SSH connection established successfully!" << std::endl;

    // Authenticate using password
    rc = authenticateByPassword(sshSession);
    if (rc != SSH_AUTH_SUCCESS) {
        std::cerr << "\r" << "Failed to authenticate: " << ssh_get_error(sshSession) << std::endl;
        std::cout << "Authentication failed. Press Enter to retry or exit." << std::endl;
        std::getline(std::cin, userInput); // Wait for user to press Enter
        ssh_disconnect(sshSession);
        ssh_free(sshSession);
        return 1;
    }

    std::cout << "\r" << "Authentication successful!" << std::endl;

    // Open SSH shell session
    rc = openShellSession(sshSession);
    if (rc != SSH_OK) {
        std::cerr << "Failed to open SSH shell session" << std::endl;
        std::cout << "Failed to open SSH shell session. Press Enter to exit." << std::endl;
        std::getline(std::cin, userInput); // Wait for user to press Enter
    }

    // Disconnect and cleanup
    ssh_disconnect(sshSession);
    ssh_free(sshSession);

    return 0;
}