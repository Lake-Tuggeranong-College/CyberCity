# Basic Sequence of events

This is a very simple overview of the process of the ESP32 Feather posting data to the PHP server.

It is the same process for each sensor posting and receiving data from the server.

```mermaid
sequenceDiagram

autonumber
participant ESP32 Feather
Actor User as User
participant PHP Server
participant MySQL Db server

ESP32 Feather ->> PHP Server: Post Temperature Sensor Data
PHP Server ->> MySQL Db server: Store data in table

User ->> PHP Server: Load ESP Data Page
PHP Server ->> MySQL Db server: Request Sensor Data
MySQL Db server ->> PHP Server: Return Sensor Data

PHP Server ->> User: Display Data in webpage

```