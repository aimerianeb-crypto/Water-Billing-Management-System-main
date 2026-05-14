const express = require('express');
const mysql = require('mysql2');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');
const cors = require('cors');
const crypto = require('crypto');
const jwt = require('jsonwebtoken');

const app = express();
app.use(cors());
app.use(express.json());

// SECURITY KEY
const JWT_SECRET = "it_student_secret_key_2026";

// DATABASE CONNECTION
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'wbms_db' 
});

db.connect(err => {
    if (err) console.error('Database connection failed:', err.stack);
    else console.log('Connected to MySQL Database via Bridge.');
});

// ==========================================
// 1. MIDDLEWARE: ANG GUARD (Security Guard)
// ==========================================
const verifyToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(403).json({ message: "No token provided! Please login first." });
    }

    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) {
            return res.status(401).json({ message: "Unauthorized! Invalid or expired token." });
        }
        req.userId = decoded.id;
        next();
    });
};

// ==========================================
// 2. SWAGGER OPTIONS 
// ==========================================
const swaggerOptions = {
    definition: {
        openapi: '3.0.0',
        info: {
            title: 'Water Billing API (Secured Bridge)',
            version: '1.0.0',
            description: 'Secured API for System Integration using Stored Procedures',
        },
        servers: [{ url: 'http://localhost:3000' }],
        components: {
            securitySchemes: {
                bearerAuth: {
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                }
            }
        },
        security: [{ bearerAuth: [] }],
        tags: [
            { name: 'Auth', description: 'Authentication operations' },
            { name: 'Client Management', description: 'Operations related to water billing clients' }
        ],
        paths:
         {
            '/api/login': {
                post: {
                    tags: ['Auth'],
                    summary: 'User Login',
                    security: [], // No lock icon here
                    requestBody: {
                        required: true,
                        content: { 'application/json': { schema: { type: 'object', properties: { username: { type: 'string' }, password: { type: 'string' } } } } }
                    },
                    responses: { 200: { description: 'Success' } }
                }
            },

            '/api/clients': 
            {
                get: {
                    tags: ['Client Management'],
                    summary: 'Get all clients',
                    responses: { 200: { description: 'Success' } }
                }
            },
            '/api/clients/add': {
                post: {
                    tags: ['Client Management'],
                    summary: 'Add a new client',
                    requestBody: {
                        required: true,
                        content: { 
                            'application/json': { 
                                schema: { 
                                    type: 'object', 
                                    properties: { 
                                        code: { type: 'string' }, category_id: { type: 'integer' }, 
                                        firstname: { type: 'string' }, middlename: { type: 'string' }, 
                                        lastname: { type: 'string' }, gender: { type: 'string' }, 
                                        birthdate: { type: 'string', format: 'date' }, contact: { type: 'string' }, 
                                        address: { type: 'string' }, purok: { type: 'string' }, first_reading: { type: 'number' } 
                                    } 
                                } 
                            } 
                        }
                    },
                    responses: { 201: { description: 'Created' } }
                }
            },

       // KINI ANG PUNO PARA SA UPDATE UG DELETE
            '/api/clients/{id}': {
                put: {
                    tags: ['Client Management'],
                    summary: 'Update an existing client',
                    parameters: [
                        { name: 'id', in: 'path', required: true, schema: { type: 'integer' } }
                    ],
                    requestBody: {
                        required: true,
                        content: {
                            'application/json': {
                                schema: {
                                    type: 'object',
                                    properties: {
                                        firstname: { type: 'string' },
                                        middlename: { type: 'string' },
                                        lastname: { type: 'string' },
                                        contact: { type: 'string' },
                                        address: { type: 'string' }
                                    }
                                }
                            }
                        }
                    },
                    responses: { 200: { description: 'Client updated via Bridge!' } }
                },
                delete: {
                    tags: ['Client Management'],
                    summary: 'Delete client',
                    parameters: [
                        { name: 'id', in: 'path', required: true, schema: { type: 'integer' } }
                    ],
                    responses: { 200: { description: 'Client permanently deleted!' } }
                }
            },
            
        // BILLING RECORDS START
            '/api/billings': {
                get: {
                    tags: ['Billing Management'],
                    summary: 'Get all billing records',
                    responses: { 200: { description: 'Success' } }
                },
                post: {
                    tags: ['Billing Management'],
                    summary: 'Create new billing record',
                    requestBody: {
                        required: true,
                        content: {
                            'application/json': {
                                schema: {
                                    type: 'object',
                                    properties: {
                                        client_id: { type: 'integer' },
                                        meter_code: { type: 'string' },
                                        reading_date: { type: 'string', format: 'date' },
                                        due_date: { type: 'string', format: 'date' },
                                        reading: { type: 'number' },
                                        previous: { type: 'number' },
                                        arrears: { type: 'number' },
                                        rate: { type: 'number' },
                                        total: { type: 'number' },
                                        status: { type: 'integer', description: '0=pending, 1=paid' }
                                    }
                                }
                            }
                        }
                    },
                    responses: { 201: { description: 'Billing Created' } }
                }
            },
            '/api/billings/{id}': {
                put: {
                    tags: ['Billing Management'],
                    summary: 'Update billing status/record',
                    parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
                    requestBody: {
                        required: true,
                        content: {
                            'application/json': {
                                schema: {
                                    type: 'object',
                                    properties: {
                                        status: { type: 'integer' },
                                        total: { type: 'number' }
                                    }
                                }
                            }
                        }
                    },
                    responses: { 200: { description: 'Billing Updated' } }
                },
                delete: {
                    tags: ['Billing Management'],
                    summary: 'Delete billing record',
                    parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
                    responses: { 200: { description: 'Billing Deleted' } }
                }
            }
        }
    },
    apis: [], 
};

const swaggerDocs = swaggerJsdoc(swaggerOptions);
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerDocs));


// ==========================================
// 3. API ROUTES (BRIDGE MODE)
// ==========================================

// 1. LOGIN
app.post('/api/login', (req, res) => {
    const { username, password } = req.body;
    const hashedPassword = crypto.createHash('md5').update(password).digest('hex');
    const sql = "CALL sp_LoginUser(?, ?)";
    
    db.query(sql, [username, hashedPassword], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        const user = results[0][0];
        if (user) {
            const token = jwt.sign({ id: user.id, username: user.username }, JWT_SECRET, { expiresIn: '1h' });
            res.json({ status: "success", token: token, user: { id: user.id, username: user.username, role: user.type } });
        } else {
            res.status(401).json({ status: "error", message: "Invalid credentials" });
        }
    });
});

// 3. [CREATE] NEW CLIENT
// Node.js side (index.js)
app.post('/api/clients/add', verifyToken, (req, res) => {
    const { category_id, code, firstname, middlename, lastname, gender, birthdate, contact, address, purok } = req.body;

    // Kinahanglan sakto ang gidaghanon sa parameters sa imong CALL
    const sql = "CALL sp_AddClient(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    const params = [category_id, code, firstname, middlename, lastname, gender, birthdate, contact, address, purok];

    db.query(sql, params, (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ status: "success", message: "Client added successfully!" });
    });
});

// 2. [READ] GET ALL CLIENTS
app.get('/api/clients', verifyToken, (req, res) => {
    const sql = "CALL sp_GetAllClients()";
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0]); 
    });
});

// [UPDATE] - Update client via Procedure
app.put('/api/clients/:id', verifyToken, (req, res) => {
    const id = req.params.id;
    const { firstname, middlename, lastname, contact, address } = req.body;
    
    const sql = "CALL sp_UpdateClient(?, ?, ?, ?, ?, ?)";
    db.query(sql, [id, firstname, middlename, lastname, contact, address], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: "Client updated via Bridge!" });
    });
});

// [DELETE] - Hard delete client via Bridge
app.delete('/api/clients/:id', verifyToken, (req, res) => {
    const id = req.params.id;
    
    // Tawgon ang procedure nga naay DELETE logic
    const sql = "CALL sp_DeleteClient(?)";
    
    db.query(sql, [id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: "Client permanently deleted from integrated database!" });
    });
});

// GET ALL CATEGORIES VIA SP
app.get('/api/categories', verifyToken, (req, res) => {
    const sql = "CALL sp_GetCategories()"; // CALL na gyud ni!
    
    db.query(sql, (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        
        // Sa MySQL, ang resulta sa CALL naa sa index [0]
        const categories = result[0]; 
        res.status(200).json(categories);
    });
});

// [GET] ALL BILLINGS
app.get('/api/billings', verifyToken, (req, res) => {
    db.query("CALL sp_GetAllBillings()", (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0]); // Ang results[0] mao ang data gikan sa SELECT
    });
});

// [POST] CREATE NEW BILLING
app.post('/api/billings', verifyToken, (req, res) => {
    const { client_id, meter_code, reading_date, due_date, reading, previous, arrears, rate, total, status } = req.body;
    const sql = "CALL sp_AddBilling(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const params = [client_id, meter_code, reading_date, due_date, reading, previous, arrears, rate, total, status];

    db.query(sql, params, (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ message: "Billing record added successfully!" });
    });
});

// [PUT] UPDATE BILLING STATUS
app.put('/api/billings/:id', verifyToken, (req, res) => {
    const id = req.params.id;
    const { status } = req.body;
    db.query("CALL sp_UpdateBillingStatus(?, ?)", [id, status], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: "Billing status updated!" });
    });
});

// [DELETE] BILLING
app.delete('/api/billings/:id', verifyToken, (req, res) => {
    const id = req.params.id;
    db.query("CALL sp_DeleteBilling(?)", [id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: "Billing record deleted!" });
    });
});

app.listen(3000, () => {
    console.log('🚀 Secured Bridge Server running on http://localhost:3000');
    console.log('📖 Swagger UI: http://localhost:3000/api-docs');
});