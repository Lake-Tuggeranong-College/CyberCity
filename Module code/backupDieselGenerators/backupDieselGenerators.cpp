#include <cstdlib> // For system function
#include <iostream>
#include <string>

#ifdef _WIN32
const std::string openCommand = "start";
#else
const std::string openCommand = "xdg-open"; // Default PDF viewer command on Linux
#endif

int main(int argc, char *argv[]) {
    // Check if username, server IP, and PDF path are provided as command-line arguments
    if (argc != 5) {
        std::cerr << "Usage: " << argv[0] << " <LTC> <10.177.200.71> </var/www/CyberCity/Challenge_Files/backupDieselGenerators/PDF1.pdf> <LTCpcgame5>" << std::endl;
        return 1;
    }

    // Extract command-line arguments
    std::string username = argv[1];
    std::string serverIP = argv[2];
    std::string pdfPath = argv[3];
    std::string password = argv[4];

    // Construct SCP command
    std::string scpCommand = "scp " + username + "@" + serverIP + ":" + pdfPath + " .";

    // Set environment variable for password (not recommended, just for demonstration)
    setenv("SSHPASS", password.c_str(), 1);

    // Execute SCP command to download the PDF file
    int scpResult = system(scpCommand.c_str());

    // Check if SCP command executed successfully
    if (scpResult == 0) {
        // Open the downloaded PDF file
        std::string openCommandFull = openCommand + " " + pdfPath;
        system(openCommandFull.c_str());
    } else {
        // SCP command failed
        // Handle error accordingly
        std::cerr << "SCP command failed" << std::endl;
        return 1;
    }

    return 0;
}
