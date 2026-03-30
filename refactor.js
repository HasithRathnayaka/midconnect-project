const fs = require('fs');
const path = require('path');

const basePath = path.join(__dirname, 'midwife');
const dashboardPath = path.join(basePath, 'dashboard.html');

let dashboardHtml = fs.readFileSync(dashboardPath, 'utf8');

// 1. Sidebar Links
dashboardHtml = dashboardHtml.replace(
    '<li><a href="counseling-session.html"><i class="fas fa-comments"></i> Counseling Sessions</a></li>',
    '<li><a href="#counseling-session"><i class="fas fa-comments"></i> Counseling Sessions</a></li>'
);
dashboardHtml = dashboardHtml.replace(
    '<li><a href="health-education-session.html"><i class="fas fa-chalkboard-teacher"></i> Health Education Session</a></li>',
    '<li><a href="#health-education-session"><i class="fas fa-chalkboard-teacher"></i> Health Education Session</a></li>'
);
dashboardHtml = dashboardHtml.replace(
    '<li><a href="emergency-responses.html"><i class="fas fa-ambulance"></i> Emergency Responses</a></li>',
    '<li><a href="#emergency-responses"><i class="fas fa-ambulance"></i> Emergency Responses</a></li>'
);

// Pages to extract
const pages = [
    {
        file: 'counseling-session.html',
        id: 'counseling-session',
        heroClass: 'counseling-hero'
    },
    {
        file: 'health-education-session.html',
        id: 'health-education-session',
        heroClass: 'health-hero'
    },
    {
        file: 'emergency-responses.html',
        id: 'emergency-responses',
        heroClass: 'emergency-hero'
    }
];

let allCss = '';
let allHtml = '';

for (const p of pages) {
    const filePath = path.join(basePath, p.file);
    if (!fs.existsSync(filePath)) continue;
    
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Extract CSS
    const styleMatch = content.match(/<style>([\s\S]*?)<\/style>/);
    if (styleMatch) {
        let css = styleMatch[1];
        // Remove active-external
        css = css.replace(/\.sidebar-menu a\.active-external\s*\{[\s\S]*?\}/g, '');
        // Rename page-hero
        css = css.replace(/\.page-hero/g, '.' + p.heroClass);
        allCss += `\n/* ${p.file} styles */\n` + css.trim() + '\n';
    }
    
    // Extract Content Section
    // From: <div class="content-section" style="display: block;">
    // To: </div> (before <script src="../js/page-transitions.js">)
    const contentMatch = content.match(/(<div class="content-section" style="display: block;">[\s\S]*?)<\/div>\s*<\/div>\s*<\/div>\s*<script src=/);
    
    if (contentMatch) {
        let sectionHtml = contentMatch[1];
        // Change display to none and add ID
        sectionHtml = sectionHtml.replace('<div class="content-section" style="display: block;">', `<div id="${p.id}" class="content-section" style="display: none;">`);
        // Rename page-hero
        sectionHtml = sectionHtml.replace(/class="page-hero"/g, `class="${p.heroClass}"`);
        allHtml += `\n<!-- ${p.id} Section -->\n` + sectionHtml.trim() + '\n</div>\n';
    }
}

// Inject CSS
dashboardHtml = dashboardHtml.replace('</style>', allCss + '\n</style>');

// Inject HTML right before patients section
dashboardHtml = dashboardHtml.replace(
    '<div id="patients" class="content-section"',
    allHtml + '\n            <div id="patients" class="content-section"'
);

// Inject Javascript
const jsInject = `
            // ================= Added form logic ================= //
            // Counseling session
            const counselingForm = document.getElementById('counselingForm');
            if (counselingForm) {
                const cInput = counselingForm.querySelector('input[name="session_datetime"]');
                if (cInput) cInput.value = new Date().toISOString().slice(0, 16);
                counselingForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Counseling session saved successfully.');
                    e.target.reset();
                    if (cInput) cInput.value = new Date().toISOString().slice(0, 16);
                });
            }

            // Health Education session
            const healthEdForm = document.getElementById('healthEdForm');
            if (healthEdForm) {
                const hInput = healthEdForm.querySelector('input[name="session_date"]');
                if (hInput) hInput.value = new Date().toISOString().slice(0, 10);
                healthEdForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Health education session recorded successfully.');
                    e.target.reset();
                    if (hInput) hInput.value = new Date().toISOString().slice(0, 10);
                });
            }

            // Emergency session
            const emergencyForm = document.getElementById('emergencyForm');
            if (emergencyForm) {
                const eInput = emergencyForm.querySelector('input[name="incident_datetime"]');
                if (eInput) eInput.value = new Date().toISOString().slice(0, 16);
                emergencyForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Emergency response entry saved.');
                    e.target.reset();
                    if (eInput) eInput.value = new Date().toISOString().slice(0, 16);
                });
            }
`;

dashboardHtml = dashboardHtml.replace(
    'document.getElementById(\'logout\').addEventListener(\'click\',',
    jsInject + '\n            document.getElementById(\'logout\').addEventListener(\'click\','
);

fs.writeFileSync(dashboardPath, dashboardHtml, 'utf8');

// Optionally delete or rename old files
/*
pages.forEach(p => {
    const fp = path.join(basePath, p.file);
    if (fs.existsSync(fp)) {
        fs.unlinkSync(fp);
    }
});
*/

console.log("Refactoring complete.");
