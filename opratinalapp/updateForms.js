const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'src', 'screens', 'admin');
const files = fs.readdirSync(dir);

files.forEach(file => {
  if (file.endsWith('FormScreen.tsx') || file.endsWith('DetailScreen.tsx')) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    // Add import if not present
    if (!content.includes('import { SafeScreen }')) {
      content = content.replace(
        "import { View, Text", 
        "import { SafeScreen } from '../../components/SafeScreen';\nimport { View, Text"
      );
      if (!content.includes('import { SafeScreen }')) {
         content = content.replace("import React", "import React from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';\n//");
      }
    }

    // Replace ScrollView root with SafeScreen
    if (content.includes('return (') && content.includes('<ScrollView')) {
      content = content.replace(/<ScrollView([^>]*)>/, '<SafeScreen scrollable style={styles.container}>');
      content = content.replace(/<\/ScrollView>/, '</SafeScreen>');
    }

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Updated ${file}`);
  }
});
