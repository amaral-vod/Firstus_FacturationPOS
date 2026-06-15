#!/bin/bash
# Configuration PostgreSQL pour Firstus_FacturationPOS
# Exécuter avec : sudo -u postgres psql -f database/setup_postgres.sql

CREATE DATABASE firstus_pos;
CREATE USER firstus WITH PASSWORD 'firstus123';
GRANT ALL PRIVILEGES ON DATABASE firstus_pos TO firstus;
\c firstus_pos
GRANT ALL ON SCHEMA public TO firstus;
