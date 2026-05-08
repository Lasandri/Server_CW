// node-api/middleware/validationMiddleware.js
// Update domain check to read from .env ALLOWED_DOMAINS

const { body, validationResult } = require('express-validator');
require('dotenv').config();

// Read allowed domains from .env and split by comma
const ALLOWED_DOMAINS = process.env.ALLOWED_DOMAINS
    ? process.env.ALLOWED_DOMAINS.split(',').map(d => d.trim())
    : ['my.westminster.ac.uk', 'westminster.ac.uk', 'iit.ac.lk'];

console.log('✅ Allowed email domains:', ALLOWED_DOMAINS);

const validateRegistration = [
    body('full_name')
        .trim()
        .notEmpty().withMessage('Full name is required')
        .isLength({ min: 2, max: 100 }).withMessage('Name must be 2-100 characters')
        .matches(/^[a-zA-Z\s'-]+$/)
        .withMessage('Name can only contain letters, spaces, hyphens, apostrophes'),

    body('email')
        .trim()
        .notEmpty().withMessage('Email is required')
        .isEmail().withMessage('Please provide a valid email address')
        .normalizeEmail({ gmail_remove_dots: false })
        .custom((value) => {
            const emailLower = value.toLowerCase();
            const isAllowed = ALLOWED_DOMAINS.some(domain =>
                emailLower.endsWith('@' + domain)
            );
            if (!isAllowed) {
                throw new Error(
                    `Only ${ALLOWED_DOMAINS.map(d => '@' + d).join(', ')} emails are allowed`
                );
            }
            return true;
        }),

    body('password')
        .notEmpty().withMessage('Password is required')
        .isLength({ min: 8 }).withMessage('At least 8 characters')
        .matches(/[A-Z]/).withMessage('At least one uppercase letter')
        .matches(/[a-z]/).withMessage('At least one lowercase letter')
        .matches(/[0-9]/).withMessage('At least one number')
        .matches(/[@$!%*?&]/).withMessage('At least one special character (@$!%*?&)'),

    body('confirm_password')
        .notEmpty().withMessage('Please confirm your password')
        .custom((value, { req }) => {
            if (value !== req.body.password) {
                throw new Error('Passwords do not match');
            }
            return true;
        }),

    (req, res, next) => {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }
        next();
    }
];

const validateLogin = [
    body('email')
        .trim()
        .notEmpty().withMessage('Email is required')
        .isEmail().withMessage('Invalid email format')
        .normalizeEmail({ gmail_remove_dots: false }),

    body('password')
        .notEmpty().withMessage('Password is required'),

    (req, res, next) => {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }
        next();
    }
];

const validateForgotPassword = [
    body('email')
        .trim()
        .notEmpty().withMessage('Email is required')
        .isEmail().withMessage('Invalid email format')
        .normalizeEmail({ gmail_remove_dots: false }),

    (req, res, next) => {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }
        next();
    }
];

const validateResetPassword = [
    body('password')
        .notEmpty().withMessage('Password is required')
        .isLength({ min: 8 }).withMessage('At least 8 characters')
        .matches(/[A-Z]/).withMessage('At least one uppercase letter')
        .matches(/[a-z]/).withMessage('At least one lowercase letter')
        .matches(/[0-9]/).withMessage('At least one number')
        .matches(/[@$!%*?&]/).withMessage('At least one special character'),

    body('confirm_password')
        .notEmpty().withMessage('Please confirm your password')
        .custom((value, { req }) => {
            if (value !== req.body.password) {
                throw new Error('Passwords do not match');
            }
            return true;
        }),

    (req, res, next) => {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }
        next();
    }
];

module.exports = {
    validateRegistration,
    validateLogin,
    validateForgotPassword,
    validateResetPassword
};