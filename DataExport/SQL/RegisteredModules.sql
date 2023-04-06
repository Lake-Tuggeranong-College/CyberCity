create table RegisteredModules
(
    ID            int auto_increment
        primary key,
    Location      text not null,
    Module        text not null,
    HashedAPIKey  text not null,
    CurrentOutput text null
);

INSERT INTO CyberCity.RegisteredModules (ID, Location, Module, HashedAPIKey, CurrentOutput) VALUES (1, 'FireDept', 'Temperature', '$2y$10$vnLXTRX2U8QfVambKfWzOu6XA6jnLZYDha5H/wIWavRRbvI02pz4K', null);
INSERT INTO CyberCity.RegisteredModules (ID, Location, Module, HashedAPIKey, CurrentOutput) VALUES (2, 'FireDeptTest', 'Temp', '$2y$10$sWJkPLvaDQX79sJYLBN18uhDZ8SxY9ClU5.PkT5439PMtpFmcjzIi', null);
