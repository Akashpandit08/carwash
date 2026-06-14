const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'src', 'screens', 'admin');
const files = fs.readdirSync(dir);

files.forEach(file => {
  if (file.endsWith('Screen.tsx') && !file.endsWith('FormScreen.tsx') && !file.endsWith('DetailScreen.tsx') && file !== 'AssignTeamScreen.tsx' && file !== 'AdminDashboardScreen.tsx') {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    if (!content.includes('import { SafeScreen }')) {
      content = content.replace("import React", "import React from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';\n//");
    }

    if (content.includes('return (') && content.includes('<View style={styles.container}>')) {
      content = content.replace(/<View style={styles\.container}>/, '<SafeScreen style={styles.container}>');
      
      // We need to replace the outermost </View> with </SafeScreen>
      // A simple regex might not work if there are nested views, so we find the last </View>
      const lastIndex = content.lastIndexOf('</View>');
      if (lastIndex !== -1) {
        content = content.substring(0, lastIndex) + '</SafeScreen>' + content.substring(lastIndex + 7);
      }
    }

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Updated list screen ${file}`);
  }
});
