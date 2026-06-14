const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'src', 'screens', 'admin');
const files = fs.readdirSync(dir);

files.forEach(file => {
  if (file.endsWith('Screen.tsx')) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    // Fix the broken import caused by the previous script
    if (content.includes("//, { useEffect, useState } from 'react';")) {
      content = content.replace(
        "import React from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';\n//, { useEffect, useState } from 'react';",
        "import React, { useEffect, useState } from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';"
      );
      fs.writeFileSync(filePath, content, 'utf8');
      console.log(`Fixed imports in ${file}`);
    } else if (content.includes("//, { useState, useEffect } from 'react';")) {
      content = content.replace(
        "import React from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';\n//, { useState, useEffect } from 'react';",
        "import React, { useState, useEffect } from 'react';\nimport { SafeScreen } from '../../components/SafeScreen';"
      );
      fs.writeFileSync(filePath, content, 'utf8');
      console.log(`Fixed imports in ${file}`);
    } else {
      // Find generic commented out imports
      const regex = /import React from 'react';\nimport \{ SafeScreen \} from '\.\.\/\.\.\/components\/SafeScreen';\n\/\/([^\n]+)/;
      const match = content.match(regex);
      if (match) {
        content = content.replace(regex, `import React${match[1]}\nimport { SafeScreen } from '../../components/SafeScreen';`);
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Fixed regex imports in ${file}`);
      }
    }
  }
});
