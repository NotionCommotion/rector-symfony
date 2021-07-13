symfony new rector-symfony
cd rector-symfony
composer require rector/rector --dev
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle

# Don't invoke on composer version?
#composer fix-cs
#composer phpstan

vi .gitignore
git rm --cached .env
vi .env
mkdir src/rector-testing

# Create test files and rector php files

vendor/bin/rector process src/rector-testing --dry-run --debug

# Works
vendor/bin/rector process src/rector-testing/SomeFile.php --dry-run --debug --config rector_FinalizeClassesWithoutChildrenRector.php

# Doesn't produce diff.
vendor/bin/rector process src/rector-testing/SomeClass.php --dry-run --debug --config rector_TypedPropertyRector.php

# Doesn't produce diff.
vendor/bin/rector process src/rector-testing/SymfonyRoute.php --dry-run --debug --config rector_AnnotationToAttributeRector.php

# Works
vendor/bin/rector process src/rector-testing/SomeAnnotation.php --dry-run --debug --config rector_DoctrineAnnotationClassToAttributeRector.php

# Doesn't work.  Unlike others, I will need to create a test fixture and add pull request.
vendor/bin/rector process src/rector-testing/SomeAnnotation2.php --dry-run --debug --config rector_DoctrineAnnotationClassToAttributeRector.php
