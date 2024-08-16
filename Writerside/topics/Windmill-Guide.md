# Windmill Guide

A Caesar cipher is a straightforward method of encoding messages. It falls under the category of substitution ciphers, where each letter in the plaintext is replaced by another letter a fixed number of positions down the alphabet.

### Encryption Process:
Choose a shift value (often called the key). This value determines how many positions each letter will be shifted.
For example, with a left shift of 3, the letter ‘A’ would be replaced by ‘D’, ‘B’ by ‘E’, and so on.
The transformation can be represented by aligning two alphabets: the plain alphabet (original order) and the cipher alphabet (shifted order).
When encrypting, a person looks up each letter of the message in the plain alphabet and writes down the corresponding letter in the cipher alphabet.

### Example:
Plaintext: “THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG”
Ciphertext (with a left shift of 3): “QEB NRFZH YOLTK CLU GRJMP LSBO QEB IXWV ALD”

### Decryption Process:
To decrypt, perform a right shift of the same value used for encryption (e.g., 3 in our example).
The replacement remains consistent throughout the message.
For instance, ‘Q’ in the ciphertext corresponds to ‘T’ in the plaintext.

![Example](Caesarcipher.png)

## Step one

[Cryptii](https://cryptii.com)

While using Cryptii, select decode in the options and input your cipher.

(You may need to adjust the shift of letters to make the output coherent)

![cryptiiexample.png](cryptiiexample.png)