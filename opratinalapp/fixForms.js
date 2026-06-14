const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'src', 'screens', 'admin');
const files = fs.readdirSync(dir);

files.forEach(file => {
  if (file.endsWith('FormScreen.tsx')) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    if (content.includes('<SafeScreen scrollable style={styles.container}>')) {
      content = content.replace(
        '<SafeScreen scrollable style={styles.container}>',
        '<View style={styles.container}>\n      <SafeScreen scrollable style={styles.content}>'
      );
      content = content.replace(
        '    </SafeScreen>\n  );\n};',
        '      </SafeScreen>\n    </View>\n  );\n};'
      );
      fs.writeFileSync(filePath, content, 'utf8');
      console.log(`Fixed ${file}`);
    }
  }
});
