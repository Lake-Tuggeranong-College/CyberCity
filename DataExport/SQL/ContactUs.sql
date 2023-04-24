create table ContactUs
(
    ID       int auto_increment
        primary key,
    Username text not null,
    Email    text not null
);

INSERT INTO CyberCity.ContactUs (ID, Username, Email) VALUES (1, 'Oliver', 'test123@gmail.com');
INSERT INTO CyberCity.ContactUs (ID, Username, Email) VALUES (2, 'Oliver', 'teser1@gmail.com');
INSERT INTO CyberCity.ContactUs (ID, Username, Email) VALUES (3, 'fef', 'test123');
INSERT INTO CyberCity.ContactUs (ID, Username, Email) VALUES (4, 'dewf', 'test12');
