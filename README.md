# Inventory REST API Demo

A synthetic inventory workflow demonstrating REST design, stock updates, validation-ready models, and role-neutral API structure.

## Features
- List and create inventory items
- Update stock quantities
- Synthetic-only records
- Automated test coverage

## Architecture
Client → REST controller → in-memory inventory repository → JSON response

## Run
```powershell
npm install
npm start
```

Default port: `4002`.
