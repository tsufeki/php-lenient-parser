if ! [[ -a ./php-7.1.0.tar.gz ]]; then
    wget -q https://github.com/php/php-src/archive/php-7.1.0.tar.gz
fi
mkdir -p ./data/php-src
tar -xzf ./php-7.1.0.tar.gz -C ./data/php-src --strip-components=1
php test_old/run.php --verbose --no-progress PHP7 ./data/php-src
