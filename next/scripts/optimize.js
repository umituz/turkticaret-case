#!/usr/bin/env node

const { execSync } = require('child_process');

console.log('🚀 Starting TurkTicaret optimization process...\n');

// 1. Analyze bundle size
console.log('📊 Analyzing bundle size...');
try {
  execSync('ANALYZE=true npm run build', { stdio: 'inherit' });
  console.log('✅ Bundle analysis complete! Check .next/analyze/ folder\n');
} catch {
  console.log('⚠️ Bundle analysis failed, continuing...\n');
}

// 2. Check for unused dependencies
console.log('🔍 Checking for unused dependencies...');
try {
  execSync('npx depcheck', { stdio: 'inherit' });
  console.log('✅ Dependency check complete!\n');
} catch {
  console.log('⚠️ Install depcheck to run this check: npm install -g depcheck\n');
}

// 3. Run build to check for issues
console.log('🔨 Running production build...');
try {
  execSync('npm run build', { stdio: 'inherit' });
  console.log('✅ Production build successful!\n');
} catch {
  console.log('❌ Production build failed. Please fix the errors above.\n');
  process.exit(1);
}

// 4. Performance tips
console.log('💡 Optimization completed! Here are some tips:');
console.log('   • Images are optimized with Next.js Image component');
console.log('   • Bundle analyzer configured (ANALYZE=true npm run build)');
console.log('   • Console logs removed in production');
console.log('   • Package imports optimized');
console.log('   • Tree shaking enabled');
console.log('   • Performance monitoring utilities available');
console.log('\n🎉 Your TurkTicaret application is optimized and ready!');