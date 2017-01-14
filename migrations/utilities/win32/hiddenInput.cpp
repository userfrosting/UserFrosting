// Compile via Visual Studio Developer Command Prompt with 'cl /O1 /EHsc hiddenInput.cpp', or prefered alternative.

#include <iostream>
#include <string>
#include <windows.h>

using namespace std;

int main()
{
    // Hide user input
    HANDLE hStdin = GetStdHandle(STD_INPUT_HANDLE);
    // Get console mode
    DWORD mode = 0;
    GetConsoleMode(hStdin, &mode);
    // Modify console mode to prevent echo of user input
    SetConsoleMode(hStdin, mode & (~ENABLE_ECHO_INPUT));

    // Get user input
    string input = "";
    getline(cin, input);

    // Restore state
    SetConsoleMode(hStdin, mode);

    // Return input
    cout << input << endl;

    return 0;
}