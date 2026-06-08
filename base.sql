CREATE DATABASE gestor_gastos;

USE gestor_gastos;

CREATE TABLE usuarios(

    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    correo VARCHAR(100) UNIQUE NOT NULL,

    password VARCHAR(255) NOT NULL,

    foto_perfil MEDIUMBLOB,

    biometrico BOOLEAN DEFAULT FALSE,

    token_biometrico VARCHAR(255) DEFAULT NULL,

    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE gastos(
    id_gasto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);