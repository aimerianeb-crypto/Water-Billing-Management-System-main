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
app.post('/api/clients/add', verifyToken, (req, res) => {
    const { 
        code, category_id, firstname, middlename, lastname, 
        gender, birthdate, contact, address, purok, first_reading 
    } = req.body;

    const sql = "CALL sp_AddClient(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const params = [code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, first_reading];

    db.query(sql, params, (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ status: "success", message: "Client added successfully via Bridge!" });
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

app.listen(3000, () => {
    console.log('🚀 Secured Bridge Server running on http://localhost:3000');
    console.log('📖 Swagger UI: http://localhost:3000/api-docs');
});