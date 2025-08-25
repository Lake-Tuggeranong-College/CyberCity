#include <stdio.h>

int junk_function_1(int x) { return x * 2; }
int junk_function_2(int x) { return x + 5; }
int junk_function_3(int x) { return x - 3; }
int junk_function_4(int x) { return x / 2; }
int junk_function_5(int x) { return x * x; }

void createFlag() {
    int a = 1, b = 2, c = 3, d = 4, e = 5, f = 6;
    int unused_variable = a + b + c + d + e + f;
    (void)unused_variable;

    for (int i = 0; i < 10; i++) {
        unused_variable += i;
    }

    for (int i = 0; i < 100; i++) {
        int temp = i * i - i + 3;
        if (temp % 2 == 0) {
            temp += junk_function_1(temp);
        } else {
            temp -= junk_function_2(temp);
        }
    }
}

void buildFlag() {
    int junk_array[100];
    for (int i = 0; i < 100; i++) {
        junk_array[i] = i * i;
    }

    for (int i = 0; i < 100; i++) {
        junk_array[i] += junk_function_3(junk_array[i]);
    }
}

// You want a prediction about the weather, you're asking the wrong Phil. I'll give you a winter prediction: It's gonna be cold, it's gonna be grey, and it's gonna last you for the rest of your life.

void locateGroundhog() {
    const char *flag = "CTF{BadWeather}";

}

int main() {
    createFlag();

    locateGroundhog();

    buildFlag();
    int counter = 0;
    for (int i = 0; i < 50; i++) {
        for (int j = 0; j < 10; j++) {
            if ((i * j) % 3 == 0) {
                counter += junk_function_4(j);
            } else {
                counter -= junk_function_5(i);
            }
        }
    }

    printf("Done\n");
    return 0;
}
