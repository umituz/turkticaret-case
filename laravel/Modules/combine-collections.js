/**
 * Postman Collection Combiner
 * Combines individual collection files into a single Postman Collection
 *
 * Usage: node combine-collections.js [project_path]
 */

import { fileURLToPath } from 'url';
import { PostmanCollectionManager } from './src/PostmanCollectionManager.js';

async function main() {
  try {
    // Get project path from arguments or use current directory
    const projectPath = process.argv[2] || process.cwd();

    // Run the collection combiner
    await PostmanCollectionManager.combineCollections(projectPath);
  } catch (error) {
    console.error(`❌ Error: ${error.message}`);
    process.exit(1);
  }
}

// Run the script when executed directly
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  main().catch(error => {
    console.error(`❌ Fatal error: ${error.message}`);
    process.exit(1);
  });
}

// Export for backward compatibility
export const combineCollections = PostmanCollectionManager.combineCollections;
export const sanitizeCollection = PostmanCollectionManager.sanitizeCollection;
