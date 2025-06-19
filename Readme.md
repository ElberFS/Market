No ejecuar 
php artisan make:livewire Category/CategoryForm
php artisan make:livewire Category/CategoryList
php artisan make:livewire Product/ProductForm
php artisan make:livewire Product/ProductList
php artisan storage:link


extension=intl 


git init
git add .
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/ElberFS/Vue.git
git push -u origin main
-- -----------------------------------------------------------------

Pasos despues de clonar  descargar y descomprimir : 
1.- Buscar el archivo  .env.example 
2.- Crear un archivo de nombre .env y van a pegar toda la informacion de .env.example 

3.- ejecutar el comando en terminal tener en cuenta que esten en la ruta de su  proyecto : Composer install

4.- ejecutar : npm install  || Siempre y cuando hagan fronted si es solo una api basica sin kits de inicio no es necesario 

5.- crear la llave de sesion : php artisan key:generate

6.- Migrar base de datos: php artisan migrate 
7.- Ejecuar Seeder : php artisan db:seed
