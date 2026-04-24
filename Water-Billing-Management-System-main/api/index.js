const express = require('express');
const mysql = require('mysql2');
const swaggerJsdoc = require('swagger-jsdoc');
const swaggerUi = require('swagger-ui-express');
const cors = require('cors');
const crypto = require('crypto');
const jwt = require('jsonwebtoken'); // Gi-import na ang gi-install nimo

const app = express();
app.use(cors());
app.use(express.json());

// SECURITY KEY - Ayaw ni i-share sa uban
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
    else console.log('Connected to MySQL Database.');
});

// ==========================================
// 1. MIDDLEWARE: ANG GUARD (Security Guard)
// ==========================================
const verifyToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Kuhaon ang token human sa word nga 'Bearer'

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
            title: 'Water Billing API (Secured)',
            version: '1.0.0',
            description: 'Secured API for System Integration - Client Management',
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
        security: [{ bearerAuth: [] }], // Global security: tanan endpoints naay lock icon
        tags: [
            {
                name: 'Client Management',
                description: 'Operations related to water billing clients'
            }
        ],

        //LOGIN
        paths: {
            '/api/login': {
                post: {
                    tags: ['Client Management'],
                    summary: 'User Login (Get Token)',
                    security: [], // WALA NI LOCK ICON para makasulod ka
                    requestBody: {
                        required: true,
                        content: { 'application/json': { schema: { type: 'object', properties: { username: { type: 'string' }, password: { type: 'string' } } } } }
                    },
                    responses: { 200: { description: 'Success' } }
                }
            },

            //CRUD CLIENTS
            '/api/clients': {
                get: { 
                    tags: ['Client Management'],
                    summary: 'View All Clients', 
                    responses: { 200: { description: 'Success' } } 
                }
            },
            '/api/clients/{id}': {
                get: { 
                    tags: ['Client Management'],
                    summary: 'View Client Details',
                    parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
                    responses: { 200: { description: 'Success' } }
                }
            },

              '/api/clients/search/{query}': {
                get: {
                    tags: ['Client Management'],
                    summary: 'Search Clients',
                    parameters: [{ name: 'query', in: 'path', required: true, schema: { type: 'string' } }],
                    responses: { 200: { description: 'Success' } }
                }
            },
            '/api/clients/add': {
                post: {
                    tags: ['Client Management'],
                    summary: 'Add New Client',
                    requestBody: {
                        required: true,
                        content: { 'application/json': { schema: { type: 'object', properties: { code: { type: 'string' }, category_id: { type: 'integer' }, firstname: { type: 'string' }, middlename: { type: 'string' }, lastname: { type: 'string' }, gender: { type: 'string' }, birthdate: { type: 'string' }, contact: { type: 'string' }, address: { type: 'string' }, purok: { type: 'string' } } } } }
                    },
                    responses: { 201: { description: 'Created' } }
                }
            },
            '/api/clients/update/{id}': {
                put: {
                    tags: ['Client Management'],
                    summary: 'Update Client',
                    parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
                    requestBody: {
                        required: true,
                        content: { 'application/json': { schema: { type: 'object', properties: { code: { type: 'string' }, category_id: { type: 'integer' }, firstname: { type: 'string' }, middlename: { type: 'string' }, lastname: { type: 'string' }, gender: { type: 'string' }, birthdate: { type: 'string' }, contact: { type: 'string' }, address: { type: 'string' }, purok: { type: 'string' } } } } }
                    },
                    responses: { 200: { description: 'Updated' } }
                }
            },
            '/api/clients/delete/{id}': {
                delete: {
                    tags: ['Client Management'],
                    summary: 'Delete Client',
                    parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
                    responses: { 200: { description: 'Deleted' } }
                }
            },

            // BILLING 
            '/api/billing/all': {
                get: {
                    tags: ['Billing Management'],
                    summary: 'View All Billing Records',
                    responses: { 200: { description: 'Success' } }
                }
            },

            '/api/billing/search/{query}': {
                get: {
                    tags: ['Billing Management'],
                    summary: 'Search Billing Records',
                    parameters: [{ name: 'query', in: 'path', required: true, schema: { type: 'string' } }],
                    responses: { 200: { description: 'Success' } }
                }
            },

            '/api/billing/add': {
    post: {
        tags: ['Billing Management'],
        summary: 'Add New Billing Record',
        requestBody: {
            required: true,
            content: { 'application/json': { schema: { type: 'object', properties: { client_id: { type: 'integer' }, meter_code: { type: 'string' }, reading_date: { type: 'string' }, due_date: { type: 'string' }, reading: { type: 'number' }, status: { type: 'integer' } } } } }
        },
        responses: { 201: { description: 'Created' } }
    }
},

//MONTHLY BILLING
'/api/reports/monthly': {
    get: {
        tags: ['Monthly Billing Management'],
        summary: 'Get Monthly Billing Report Summary',
        parameters: [
            { name: 'month', in: 'query', required: true, schema: { type: 'integer' }, description: '1-12' },
            { name: 'year', in: 'query', required: true, schema: { type: 'integer' }, description: 'e.g. 2026' }
        ],
        responses: { 200: { description: 'Success' } }
    }
},

//USERS
'/api/users': {
    get: {
        tags: ['User Management'], // I-grupo nalang nato diri para dili mag-error
        summary: 'View All System Users',
        responses: { 200: { description: 'Success' } }
    }
},

//CATEGORIES
'/api/categories': {
    get: {
        tags: ['Category Management'],
        summary: 'View All Water Categories and Rates',
        responses: { 200: { description: 'Success' } }
    }
},
'/api/categories/update/{id}': {
    put: {
        tags: ['Category Management'],
        summary: 'Update Water Rate (Admin Only)',
        parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
        requestBody: {
            required: true,
            content: { 'application/json': { schema: { type: 'object', properties: { 
                name: { type: 'string' }, 
                rate: { type: 'number' } 
            } } } }
        },
        responses: { 200: { description: 'Updated' } }
    }
},

//USERS
'/api/users/add': {
    post: {
        tags: ['User Management'],
        summary: 'Register New User',
        requestBody: {
            required: true,
            content: { 'application/json': { schema: { type: 'object', properties: { 
                firstname: { type: 'string' },
                middlename: { type: 'string' },
                lastname: { type: 'string' },
                username: { type: 'string' }, 
                password: { type: 'string' }, 
                type: { type: 'integer', description: '1 for Admin, 2 for Staff' } 
            } } } }
        },
        responses: { 201: { description: 'User Created' } }
    }
},
'/api/users/delete/{id}': {
    delete: {
        tags: ['User Management'],
        summary: 'Delete System User',
        parameters: [{ name: 'id', in: 'path', required: true, schema: { type: 'integer' } }],
        responses: { 200: { description: 'Deleted' } }
    }
}
        }
    },
    apis: [], 
};

const swaggerDocs = swaggerJsdoc(swaggerOptions);
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerDocs));

// ==========================================
// 3. API ROUTES
// ==========================================

// 1. LOGIN (Mo-hatag og Token)
// UPDATE sa imong /api/login sa index.js
app.post('/api/login', (req, res) => {
    const { username, password } = req.body;
    const hashedPassword = crypto.createHash('md5').update(password).digest('hex');
    
    // Siguraduha nga gi-select nimo ang 'id', 'username', ug 'role'
    const sql = "SELECT id, username, type FROM users WHERE username = ? AND password = ?";
    
    db.query(sql, [username, hashedPassword], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        
        if (results.length > 0) {
            const user = results[0];
            const token = jwt.sign({ id: user.id, username: user.username }, JWT_SECRET, { expiresIn: '1h' });
            
            res.json({ 
                status: "success", 
                token: token, 
                user: {
                    id: user.id,
                    username: user.username,
                    role: user.type // I-map nato ang 'type' padulong sa 'role' para sa C#
                }
            });
        } else {
            res.status(401).json({ status: "error", message: "Invalid credentials" });
        }
    });
});

// 2. VIEW ALL CLIENTS (Secured)
app.get('/api/clients', verifyToken, (req, res) => {
    const sql = "SELECT id, code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, status FROM client_list ORDER BY id DESC";
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 3. GET SINGLE CLIENT (Secured)
app.get('/api/clients/:id', verifyToken, (req, res) => {
    const sql = "SELECT id, code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, status FROM client_list WHERE id = ?";
    db.query(sql, [req.params.id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        if (result.length === 0) return res.status(404).json({ message: "Client not found" });
        res.json(result[0]);
    });
});

// 4. ADD CLIENT (Secured)
app.post('/api/clients/add', verifyToken, (req, res) => {
    const { code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok } = req.body;
    const sql = `INSERT INTO client_list (code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, status, delete_flag) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)`;
    const values = [code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok];

    db.query(sql, values, (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ status: "success", message: "Client added successfully", id: result.insertId });
    });
});

// 5. SEARCH CLIENTS (Secured)
app.get('/api/clients/search/:query', verifyToken, (req, res) => {
    const search = `%${req.params.query}%`;
    const sql = `SELECT id, code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, status 
                 FROM client_list WHERE lastname LIKE ? OR firstname LIKE ? OR code LIKE ?`;
    
    db.query(sql, [search, search, search], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 6. UPDATE CLIENT (Secured)
app.put('/api/clients/update/:id', verifyToken, (req, res) => {
    const { code, category_id, firstname, lastname, middlename, gender, birthdate, contact, address, purok } = req.body;
    const sql = `UPDATE client_list SET code=?, category_id=?, firstname=?, lastname=?, middlename=?, 
                gender=?, birthdate=?, contact=?, address=?, purok=? WHERE id=?`;
    
    db.query(sql, [code, category_id, firstname, lastname, middlename, gender, birthdate, contact, address, purok, req.params.id], (err, result) => {
        if (err) return res.status(500).json(err);
        res.json({ message: "Updated successfully" });
    });
});

// 7. DELETE CLIENT (Secured)
app.delete('/api/clients/delete/:id', verifyToken, (req, res) => {
    const sql = "DELETE FROM client_list WHERE id = ?";
    db.query(sql, [req.params.id], (err, result) => {
        if (err) return res.status(500).json(err);
        res.json({ message: "Deleted successfully" });
    });
});

// 1. VIEW ALL BILLING (Gi-join ang client_list para naay Client Name)
app.get('/api/billing/all', verifyToken, (req, res) => {
    const sql = `
        SELECT 
            b.id, 
            CONCAT(c.firstname, ' ', c.lastname) AS client_name, 
            b.meter_code, 
            CONCAT(b.previous, ' -> ', b.reading) AS reading_range,
            (b.reading - b.previous) * b.rate AS current_amount,
            b.arrears,
            b.total AS grand_total,
            CASE WHEN b.status = 1 THEN 'PAID' ELSE 'PENDING' END AS status_text
        FROM billing_list b
        JOIN client_list c ON b.client_id = c.id
        ORDER BY b.reading_date DESC`;

    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 2. SEARCH BILLING
app.get('/api/billing/search/:query', verifyToken, (req, res) => {
    const search = `%${req.params.query}%`;
    const sql = `
        SELECT 
            b.id, 
            CONCAT(c.firstname, ' ', c.lastname) AS client_name, 
            b.meter_code, 
            CONCAT(b.previous, ' -> ', b.reading) AS reading_range,
            (b.reading - b.previous) * b.rate AS current_amount,
            b.arrears,
            b.total AS grand_total,
            CASE WHEN b.status = 1 THEN 'PAID' ELSE 'PENDING' END AS status_text
        FROM billing_list b
        JOIN client_list c ON b.client_id = c.id
        WHERE c.firstname LIKE ? OR c.lastname LIKE ? OR b.meter_code LIKE ?
        ORDER BY b.reading_date DESC`;
    
    db.query(sql, [search, search, search], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// ADD NEW BILLING RECORD (Secured with Running Balance Logic)
app.post('/api/billing/add', verifyToken, (req, res) => {
    const { client_id, meter_code, reading_date, due_date, reading, status } = req.body;

    // 1. Kuhaon ang Rate base sa Category sa Client
    const sqlRate = `SELECT c.rate FROM category_list c 
                     JOIN client_list cl ON cl.category_id = c.id 
                     WHERE cl.id = ?`;

    db.query(sqlRate, [client_id], (err, rateResult) => {
        if (err || rateResult.length === 0) return res.status(500).json({ error: "Rate not found" });
        const rate = rateResult[0].rate;

        // 2. Kuhaon ang Previous Reading
        const sqlPrev = "SELECT reading FROM billing_list WHERE client_id = ? ORDER BY id DESC LIMIT 1";
        db.query(sqlPrev, [client_id], (err, prevResult) => {
            const previous = (prevResult.length > 0) ? prevResult[0].reading : 0;

            // Validation: Dili puyde mas ubos ang current sa previous
            if (parseFloat(reading) < previous) {
                return res.status(400).json({ error: `Current reading (${reading}) cannot be less than previous (${previous})!` });
            }

            // 3. Kuhaon ang Latest Arrears (Unpaid Balance)
            const sqlArrears = "SELECT total FROM billing_list WHERE client_id = ? AND status = 0 ORDER BY id DESC LIMIT 1";
            db.query(sqlArrears, [client_id], (err, arrearsResult) => {
                const total_arrears = (arrearsResult.length > 0) ? arrearsResult[0].total : 0;

                // 4. Calculations
                const current_bill = (parseFloat(reading) - previous) * rate;
                const grand_total = current_bill + total_arrears;

                // 5. I-save na sa Database
                const sqlInsert = `INSERT INTO billing_list 
                    (client_id, meter_code, reading_date, due_date, reading, previous, rate, arrears, total, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;
                
                const values = [client_id, meter_code, reading_date, due_date, reading, previous, rate, total_arrears, grand_total, status];

                db.query(sqlInsert, values, (err, result) => {
                    if (err) return res.status(500).json({ error: err.message });
                    res.status(201).json({ 
                        status: "success", 
                        message: "Billing record saved!",
                        grand_total: grand_total 
                    });
                });
            });
        });
    });
});

app.get('/api/reports/monthly', verifyToken, (req, res) => {
    const { month, year } = req.query;

    if (!month || !year) {
        return res.status(400).json({ error: "Month and Year are required." });
    }

    // SQL query nga kaparehas sa imong PHP logic
    const sql = `
        SELECT 
            b.id, 
            c.firstname, 
            c.lastname, 
            b.meter_code, 
            b.reading_date, 
            b.previous, 
            b.reading, 
            b.rate, 
            (b.reading - b.previous) * b.rate AS current_bill,
            b.arrears,
            b.total, 
            b.status
        FROM billing_list b
        JOIN client_list c ON b.client_id = c.id
        WHERE MONTH(b.reading_date) = ? AND YEAR(b.reading_date) = ?
        ORDER BY c.lastname ASC
    `;

    db.query(sql, [month, year], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// VIEW ALL USERS logic
app.get('/api/users', verifyToken, (req, res) => {
    const sql = "SELECT id, firstname, middlename, lastname, username, type, date_added FROM users WHERE type IN (1, 2) ORDER BY id DESC";
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// REGISTER NEW USER (With Role Check)
app.post('/api/users/add', verifyToken, (req, res) => {
    // Kinahanglan i-select nato ang 'type' sa login session/token
    // Para simple, i-check nato kon ang naggamit sa API kay Admin (type 1)
    const requesterId = req.userId; // Gikan sa verifyToken middleware

    // I-verify sa DB kon Admin ba ang requester
    db.query("SELECT type FROM users WHERE id = ?", [requesterId], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        
        if (results.length > 0 && results[0].type === 1) {
            // ADMIN SYA -> Proceed sa registration
            const { firstname, middlename, lastname, username, password, type } = req.body;
            const hashedPassword = crypto.createHash('md5').update(password).digest('hex');
            
            const sql = "INSERT INTO users (firstname, middlename, lastname, username, password, type) VALUES (?, ?, ?, ?, ?, ?)";
            db.query(sql, [firstname, middlename, lastname, username, hashedPassword, type], (err, result) => {
                if (err) return res.status(500).json({ error: err.message });
                res.status(201).json({ status: "success", message: "New user registered!" });
            });
        } else {
            // STAFF/SECRETARY RA SYA -> Forbidden
            res.status(403).json({ status: "error", message: "Access Denied: Admins only!" });
        }
    });
});

// DELETE USER logic
app.delete('/api/users/delete/:id', verifyToken, (req, res) => {
    const sql = "DELETE FROM users WHERE id = ?";
    db.query(sql, [req.params.id], (err, result) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ status: "success", message: "User deleted" });
    });
});

app.get('/api/categories', verifyToken, (req, res) => {
    // Base sa imong database screenshot: category_list table
    const sql = "SELECT id, name, status, rate FROM category_list WHERE delete_flag = 0";
    db.query(sql, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// 2. UPDATE CATEGORY RATE (Admin Only)
app.put('/api/categories/update/:id', verifyToken, (req, res) => {
    const requesterId = req.userId;

    // I-check nato kon Admin (type 1) ba ang nag-login
    db.query("SELECT type FROM users WHERE id = ?", [requesterId], (err, userResults) => {
        if (err) return res.status(500).json({ error: err.message });

        if (userResults.length > 0 && userResults[0].type === 1) {
            const { name, rate } = req.body;
            const sql = "UPDATE category_list SET name = ?, rate = ? WHERE id = ?";
            
            db.query(sql, [name, rate, req.params.id], (err, result) => {
                if (err) return res.status(500).json({ error: err.message });
                res.json({ status: "success", message: "Water rate updated successfully!" });
            });
        } else {
            // Kon dili Admin (sama ni Secretary Carmelle o Sophie)
            res.status(403).json({ status: "error", message: "Forbidden: Only admins can change water rates." });
        }
    });
});

app.listen(3000, () => {
    console.log('🚀 Secured Server running on http://localhost:3000');
    console.log('📖 Swagger UI: http://localhost:3000/api-docs');
});