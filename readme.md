## Сборка и запуск Docker образа

*Весь проект в одном образе, для простоты запуска. 
База sqlite, базовый образ php:7.3-apache. Сборка с использованием multi-stage.
Тесты PHPUnit выполняются при сборке контейнера, следовательно если тесты не пройдут,
контейнер не соберется.*

### Сборка образа

Занчения по умолчанию:
- Вместимость контейнера: 10
- Количество товаров: 100
- Количество контейнеров: 1000

```
docker build -t lamoda:virp .
```

Параметры сборки:
- CONTAINER_CAPACITY - вместимость контейнера
- SEED_PRODUCTS_COUNT - генерируемое кол-во товаров
- SEED_CONTAINERS_COUNT - генерируемое кол-во контейнеров

```
docker build --build-arg CONTAINER_CAPACITY=2 --build-arg SEED_PRODUCTS_COUNT=20 --build-arg SEED_CONTAINERS_COUNT=200 -t lamoda:virp .
```

### Запуск контейнера
Запуск с параметрами заданными при сборке образа:
```
docker run -d --rm --name lamoda-virp -p 8000:80 lamoda:virp
```

Переопределить параметры заданные при сборке:
```
docker run -d --rm --name lamoda-virp -p 8000:80 -e CONTAINER_CAPACITY=2 -e SEED_PRODUCTS_COUNT=20 -e SEED_CONTAINERS_COUNT=200 lamoda:virp
```

### Генерация новых продуктов и контейнеров
```
docker exec lamoda-virp php artisan migrate:fresh --force --seed
```

## Использованное решение головоломки
Документация к API: http://127.0.0.1:8000/docs

Берется первый попавшийся контейнер с товарами, из списка контейнеров удаляются все контейнера содержащие данные товары,
и так пока не выбраны все товары. Не думаю что это будет самый короткий список контейнеров,
но не сумел найти решение не подразумевающее перебор всех комбинаций.
При параметрах по умолчанию в среднем выбирается не более 30 контейнеров.

## PHP-головоломка

В Lamoda есть собственная фотостудия, на которой мы делаем фотосъемку всех новых товаров. 
Товары приходят к нам на наш [склад](https://habr.com/ru/company/lamoda/blog/432394/), 
после чего отправляются в контейнерах на фотостудию. Мы не фотографируем несколько раз один и тот же товар. 
Помоги Lamoda оптимизировать логистику между фотостудией и складом.

Игроку необходимо написать систему (веб-сервис), который будет иметь REST JSON API для:

1. Создания контейнеров (id контейнера, название, состав - массив товаров (id товара, название);
2. Получения списка контейнеров и отдельного контейнера по ID.

C помощью этого API решить следующую головоломку: есть 1000 контейнеров, в каждом
из них по 10 товаров, среди всех этих товаров только 100 уникальных, все остальные
повторяются. Нужно вернуть список контейнеров, которые содержат все 100 уникальных
товаров хотя бы по одному. Товары в контейнерах распределены случайным, но
известным образом.

*\*Эта ситуация основана на вымышленном условии, что нам нельзя вскрывать контейнеры и что все товары перемешаны.*

**Даст вам преимущество перед другими игроками:**

1. Простота разворачивания и запуска;
2. Формирование OpenAPI v3 спецификации на созданное API;
3. Применение виртуализации (Docker etc.);
4. Если ваше решение будет параметризованно количеством контейнеров и
количеством уникальных товаров и емкостью контейнера;
5. Если список возвращаемых контейнеров будет минимальным из всех возможных;
6. Если вы дополните решение аргументацией, почему ваш алгоритм оптимален по
сложности;
7. Если ваше решение будет дополнено генератором контейнеров и товаров;
8. Наличие тестов в достаточном объеме.

Фреймворк, библиотеки - на выбор игрока.

У этой головоломки может быть множество решений, и мы примем от вас любое.
