sqlplus sys/root as sysdba


-- Crear PDB Central
CREATE PLUGGABLE DATABASE pdb_central
ADMIN USER admin_pdb IDENTIFIED BY admin123
FILE_NAME_CONVERT=('D:\PROGRAMAS\ORADATA\XE\PDBSEED',
                   'D:\PROGRAMAS\ORADATA\XE\PDB_CENTRAL');

-- Abrir PDB
ALTER PLUGGABLE DATABASE pdb_central OPEN;
ALTER SESSION SET CONTAINER = pdb_central;

CREATE TABLESPACE ts_central
DATAFILE 'D:\PROGRAMAS\ORADATA\XE\PDB_CLIENTE\ts_central.dbf'
SIZE 100M AUTOEXTEND ON NEXT 50M MAXSIZE UNLIMITED;

-- Crear usuario
CREATE USER usuario_central IDENTIFIED BY central123
DEFAULT TABLESPACE ts_central
QUOTA UNLIMITED ON ts_central
TEMPORARY TABLESPACE temp;

-- Otorgar privilegios
GRANT CREATE SESSION, CREATE TABLE, INSERT ANY TABLE TO usuario_central;
GRANT SELECT ANY TABLE, UPDATE ANY TABLE TO usuario_central;
GRANT CREATE SEQUENCE TO usuario_central;

-- Guardar estado de la PDB
ALTER PLUGGABLE DATABASE pdb_central SAVE STATE;

GRANT RESTRICTED SESSION TO usuario_central;
GRANT CREATE DATABASE LINK TO usuario_central;

-- Desde cualquier PDB, volver al root
ALTER SESSION SET CONTAINER = CDB$ROOT;

-- Crear PDB Cliente
CREATE PLUGGABLE DATABASE pdb_cliente
ADMIN USER admin_pdb IDENTIFIED BY admin123
FILE_NAME_CONVERT=('D:\PROGRAMAS\ORADATA\XE\PDBSEED',
                   'D:\PROGRAMAS\ORADATA\XE\PDB_CLIENTE');

-- Abrir PDB
ALTER PLUGGABLE DATABASE pdb_cliente OPEN;
ALTER SESSION SET CONTAINER = pdb_cliente;

CREATE TABLESPACE ts_clientes
DATAFILE 'D:\PROGRAMAS\ORADATA\XE\PDB_CLIENTE\ts_clientes.dbf'
SIZE 100M AUTOEXTEND ON NEXT 50M MAXSIZE UNLIMITED;

-- Crear usuario
CREATE USER usuario_cliente IDENTIFIED BY cliente123
DEFAULT TABLESPACE ts_clientes
QUOTA UNLIMITED ON ts_clientes
TEMPORARY TABLESPACE temp;

-- Otorgar privilegios
GRANT CREATE SESSION, CREATE TABLE, INSERT ANY TABLE TO usuario_cliente;
GRANT SELECT ANY TABLE, UPDATE ANY TABLE TO usuario_cliente;
GRANT CREATE SEQUENCE TO usuario_cliente;

-- Guardar estado de la PDB
ALTER PLUGGABLE DATABASE pdb_cliente SAVE STATE;
GRANT RESTRICTED SESSION TO usuario_cliente;

sqlplus usuario_cliente/cliente123@localhost:1521/pdb_cliente

-- Tabla de clientes
CREATE TABLE clientes (
                          id NUMBER PRIMARY KEY,
                          nombre VARCHAR2(100) NOT NULL,
                          email VARCHAR2(100) UNIQUE,
                          telefono VARCHAR2(20),
                          fecha_registro DATE DEFAULT SYSDATE,
                          activo CHAR(1) DEFAULT 'S'
);

-- Crear secuencia para el ID automático
CREATE SEQUENCE seq_clientes
    START WITH 1
    INCREMENT BY 1
    NOCACHE NOCYCLE;

INSERT INTO clientes (id, nombre, email, telefono)
VALUES (seq_clientes.NEXTVAL, 'Juan Pérez', 'juan.perez@email.com', '555-1234');

INSERT INTO clientes (id, nombre, email, telefono)
VALUES (seq_clientes.NEXTVAL, 'María García', 'maria.garcia@email.com', '555-5678');

INSERT INTO clientes (id, nombre, email, telefono)
VALUES (seq_clientes.NEXTVAL, 'Carlos López', 'carlos.lopez@email.com', '555-9012');

INSERT INTO clientes (id, nombre, email, telefono)
VALUES (seq_clientes.NEXTVAL, 'Ana Rodríguez', 'ana.rodriguez@email.com', '555-3456');

INSERT INTO clientes (id, nombre, email, telefono)
VALUES (seq_clientes.NEXTVAL, 'Pedro Martínez', 'pedro.martinez@email.com', '555-7890');

COMMIT;

sqlplus usuario_central/central123@localhost:1521/pdb_central

CREATE DATABASE LINK cli
CONNECT TO usuario_cliente IDENTIFIED BY cliente123
USING '(DESCRIPTION=
          (ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))
          (CONNECT_DATA=(SERVICE_NAME=pdb_cliente)))';

SELECT * FROM usuario_cliente.clientes@cli;
