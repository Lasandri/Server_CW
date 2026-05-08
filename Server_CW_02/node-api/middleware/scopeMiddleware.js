/**
 * API Key Scope Middleware - CW2
 * 
 * Enforces permission scoping per CW2 requirements:
 * 
 * Analytics Dashboard: read:alumni, read:analytics
 * Mobile AR App:       read:alumni_of_day
 * 
 * A compromised analytics key CANNOT access AR endpoints
 * A compromised AR key CANNOT access analytics endpoints
 */

/**
 * Require specific scope
 * @param {string} requiredScope
 */
function requireScope(requiredScope) {
    return (req, res, next) => {
        if (!req.apiKey) {
            return res.status(401).json({
                status: 'error',
                message: 'Authentication required.',
            });
        }

        const scopes = req.apiKey.scopes || [];

        if (!scopes.includes(requiredScope)) {
            return res.status(403).json({
                status: 'error',
                message: `Access denied. Required scope: ${requiredScope}`,
                your_scopes: scopes,
                hint: `This API key does not have the '${requiredScope}' permission.`,
            });
        }

        next();
    };
}

/**
 * Block AR app from analytics endpoints
 * If key has ONLY read:alumni_of_day - block analytics
 */
function blockArAppFromAnalytics(req, res, next) {
    if (!req.apiKey) {
        return res.status(401).json({
            status: 'error',
            message: 'Authentication required.',
        });
    }

    const scopes = req.apiKey.scopes || [];

    // If key ONLY has alumni_of_day scope - no analytics access
    if (scopes.includes('read:alumni_of_day') &&
        !scopes.includes('read:analytics') &&
        !scopes.includes('read:alumni')) {
        return res.status(403).json({
            status: 'error',
            message: 'AR App API keys cannot access analytics endpoints.',
            your_scopes: scopes,
        });
    }

    next();
}

/**
 * Block analytics dashboard from AR endpoints
 */
function blockAnalyticsDashboardFromAr(req, res, next) {
    if (!req.apiKey) {
        return res.status(401).json({
            status: 'error',
            message: 'Authentication required.',
        });
    }

    const scopes = req.apiKey.scopes || [];

    // If key has analytics scope but NOT alumni_of_day - block AR
    if ((scopes.includes('read:analytics') || 
         scopes.includes('read:alumni')) &&
        !scopes.includes('read:alumni_of_day')) {
        return res.status(403).json({
            status: 'error',
            message: 'Analytics Dashboard keys cannot access AR endpoints.',
            your_scopes: scopes,
        });
    }

    next();
}

module.exports = {
    requireScope,
    blockArAppFromAnalytics,
    blockAnalyticsDashboardFromAr,
};