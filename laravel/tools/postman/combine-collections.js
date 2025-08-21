/**
 * Script to combine individual collection files into a single Postman Collection
 * This script uses the shared code from Modules/Postman with project-specific configuration
 *
 * Usage: node combine-collections.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../..');
const modulePath = path.join(projectRoot, 'Modules');

// Check if the Postman module exists
if (!fs.existsSync(modulePath)) {
  console.error(`❌ Error: Postman module not found at ${modulePath}`);
  console.log('Please ensure the Postman module exists at the correct location.');
  process.exit(1);
}

// Execute the module script with the current project path
try {
  const command = `node ${path.join(modulePath, 'combine-collections.js')} ${__dirname}`;
  console.log(`🚀 Running: ${command}`);
  execSync(command, { stdio: 'inherit', cwd: __dirname });
} catch (error) {
  console.error(`❌ Error executing Postman module script: ${error.message}`);
  process.exit(1);
}

console.log('✅ Collection generation completed successfully');
