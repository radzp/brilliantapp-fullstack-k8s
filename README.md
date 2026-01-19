# brilliantapp-fullstack-k8s


## CZĘŚĆ OBOWIĄZKOWA 
## Opis aplikacji
Aplikacja "BrilliantApp" to system zbudowany w oparciu o klasyczny stack LAMP (Linux, Apache, MySQL/MariaDB, PHP).
Wyświetla adres IP Poda oraz status połączenia z bazą.

Zastosowano architekturę mikroserwisową, gdzie baza danych i serwer aplikacji działają jako niezależne Pody, komunikujące się przez wewnętrzny DNS klastra (mysql-service).

Aplikacja PHP jest bezstanowa, a jej kod dostarczany jest dynamicznie przez obiekt ConfigMap, co umożliwia aktualizację kodu bez przebudowywania obrazów Docker.

## Specyfika wdrożenia
Ze względu na środowisko macos:
1. Użyłam bazy **MariaDB 10.6** zamiast MySQL 5.7.
2. Dostęp do domeny `brilliantapp.zad` zrealizowałam przez **Minikube Tunnel** mapując domenę na `127.0.0.1` w `/etc/hosts`.
3. Zbudowałam własny obraz Docker `kubernetes-lamp:v1` w celu dodania rozszerzenia `mysqli`.

## Pliki w repozytorium
* `00-namespace.yaml` - Definicja przestrzeni nazw.
* `01-secret.yaml` - Hasła do bazy.
* `02-configmap.yaml` - Kod aplikacji.
* `03-mysql.yaml` - Wdrożenie bazy danych.
* `04-webapp.yaml` - Wdrożenie aplikacji z sondami.
* `05-ingress.yaml` - Konfiguracja wejścia.
* `Dockerfile` - Definicja obrazu.
* `index.php` - Kod źródłowy.

```minikube start```

<img width="689" height="298" alt="Zrzut ekranu 2026-01-19 o 9 49 27 PM" src="https://github.com/user-attachments/assets/5f4e2b2e-0c21-4849-a9ea-f5a13c0c687c" />

```minikube addons enable ingress```

<img width="696" height="278" alt="Zrzut ekranu 2026-01-19 o 9 49 46 PM" src="https://github.com/user-attachments/assets/da972707-27f5-4edb-a5ae-7d7c90ace056" />


```minikube image build -t kubernetes-lamp:v1 .```
<img width="687" height="456" alt="Zrzut ekranu 2026-01-19 o 9 50 12 PM" src="https://github.com/user-attachments/assets/06c04e27-5083-4c66-a650-27975d354cf7" />

```kubectl apply -f 01-namespace.yaml```
```kubectl apply -f 02-secret.yaml```
<img width="671" height="162" alt="Zrzut ekranu 2026-01-19 o 9 51 02 PM" src="https://github.com/user-attachments/assets/37b17a5f-9c57-4d20-9644-795e5d2e2e43" />


```kubectl create configmap app-code --from-file=index.php -n brilliant-ns --dry-run=client -o yaml | kubectl apply -f -```
<img width="728" height="82" alt="Zrzut ekranu 2026-01-19 o 9 52 45 PM" src="https://github.com/user-attachments/assets/73bb2dda-ad4a-42a1-b5aa-55d1a96b32ec" />

``` kubectl apply -f 03-mysql.yaml```
``` kubectl apply -f 04-webapp.yaml```
<img width="583" height="212" alt="Zrzut ekranu 2026-01-19 o 9 53 54 PM" src="https://github.com/user-attachments/assets/dabcd71b-797c-4cb5-b055-eaffbd45ab3c" />

```kubectl apply -f 05-ingress.yaml ```
<img width="584" height="80" alt="Zrzut ekranu 2026-01-19 o 9 54 25 PM" src="https://github.com/user-attachments/assets/8a001587-b78d-443b-b301-40d5b1bc3e2e" />

``` sudo nano /etc/hosts ```
Edycja pliku hosts
<img width="734" height="474" alt="Zrzut ekranu 2026-01-19 o 9 56 23 PM" src="https://github.com/user-attachments/assets/8ba516ef-d45c-475d-8284-a5b0919d8d3c" />

Użyłam minikube tunnel, ponieważ srodowisko wykonawcze to macos i nie ma bezpośredniego dostepu do komendy minikube ip: 

<img width="519" height="301" alt="Zrzut ekranu 2026-01-19 o 9 58 26 PM" src="https://github.com/user-attachments/assets/6b798d51-e6cd-44bd-a35f-8dec12f55498" />



Wygląd aplikacji przed: 
<img width="1833" height="980" alt="Zrzut ekranu 2026-01-19 o 9 56 01 PM" src="https://github.com/user-attachments/assets/c5c84403-eb62-4c2c-bb9a-01f03b5a1832" />
``
## CZĘŚĆ NIEOBOWIĄZKOWA
<img width="717" height="76" alt="Zrzut ekranu 2026-01-19 o 10 06 09 PM" src="https://github.com/user-attachments/assets/e7900ee4-fedb-4c3d-aa2e-f349ea04bb6e" />


``` kubectl rollout restart deployment/webapp-deployment -n brilliant-ns ```
<img width="656" height="58" alt="Zrzut ekranu 2026-01-19 o 10 09 22 PM" src="https://github.com/user-attachments/assets/d89660c6-7981-4c38-8b2e-1a3420e27d6b" />

``` kubectl rollout status deployment/webapp-deployment -n brilliant-ns ``` 
<img width="729" height="138" alt="Zrzut ekranu 2026-01-19 o 10 10 24 PM" src="https://github.com/user-attachments/assets/08781e1c-e933-4f53-b514-c1d35372ff8a" />

# Dodanie sond - zmiana w pliku 04-webapp.yaml
<img width="409" height="267" alt="Zrzut ekranu 2026-01-19 o 10 08 10 PM" src="https://github.com/user-attachments/assets/a4bba8d0-e083-46e6-be6e-a17db060c805" />

``` kubectl apply -f 04-webapp.yaml ```
<img width="590" height="102" alt="Zrzut ekranu 2026-01-19 o 10 08 33 PM" src="https://github.com/user-attachments/assets/e36a1e70-0d02-4f24-b136-6e4601d047a8" />

## Uzasadnienie Sond 
* **LivenessProbe (HTTP):** Sprawdza kod 200 na ścieżce `/`. onitoruje czy serwer Apache odpowiada. W przypadku zawieszenia procesu lub błędu krytycznego serwera, Kubernetes automatycznie zrestartuje kontener, przywracając działanie usługi.
* **ReadinessProbe (TCP):** Weryfikuje, czy kontener zakończył proces startowy i jest gotowy na przyjęcie ruchu. Zapobiega kierowaniu użytkowników do instancji, która jeszcze się inicjalizuje, co eliminuje błędy połączeń podczas startu lub skalowania.

Wygląd aplikacji po:
<img width="1839" height="979" alt="Zrzut ekranu 2026-01-19 o 10 05 27 PM" src="https://github.com/user-attachments/assets/06460495-4848-44a5-91a2-af078ca71a98" />




