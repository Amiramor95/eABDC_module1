# Module Template

## Instruction for devs
1. Run ```composer update``` after fetch latest code or when any bugs occured.
2. Run ```php artisan optimize:clear``` to clear cache.
3. Fill in models array inside ```modelGenerator.php```.
4. Run ```php artisan generate:models``` to generate model & controller.

## Module Checklist
Each module shall contain below requirement.
 - [x] Keycloak Authorization
 - [x] LDAP
 - [ ] Simple Unit Testing (Pending)
 - [x] API Docs
 - [ ] Error Status Code (Ongoing)
 - [ ] Synchronize updated users to  AD

**Steps To Run API Doc**

 - Run ```php artisan optimize:clear``` on terminal to clear cache.
 - Run ```php artisan generate:docs``` on terminal to generate api docs.
 - View your api generator on ```http://localhost:xxxx/api-docs```.

**Sample Route**

Please refer https://fimm-dev.postman.co/workspace/FiMM-Backend-Development and http://localhost:xxxx/api-docs for more availabled APIs.


Below are the list that can be tested (if passed).

| Module| Route | Method | Function |Status | Body Parameter | URL
| ---| ------ | ------ | ------ | ------ | ------ | ------ |
| User Management | /api/module0/login| POST | Login User | Pass | [Login](#login-sample-parameter) |http://localhost:7000/api/module0/login |
| User Management | /api/module0/logout| POST | Logout User | Pass |[Logout](#logout-sample-parameter)|http://localhost:7000/api/module0/logout |
| LDAP Management | /api/module0/ldap_testing| POST | Test LDAP | Pass | [LDAP](#ldap-sample-parameter) |http://localhost:7000/api/module0/ldap_testing |

### Login Sample Parameter

    {"login_id":"ahmad","email":"ahmad@gmail.com","password":"abc123"}

### Logout Sample Parameter

    {"user_id":"e7aa251c-0fa7-4748-9a60-1f2c23732ae0"}

### Ldap Sample Parameter

    {"bindCredential":"@Bcd1234", "bindDn":"CN=dummy,OU=HR Admin,DC=ad,DC=vn,DC=my", "connectionUrl":"ldap://192.168.3.199:389"}

### Create User Sample Parameter

    {"login_id":"ahmad","email":"ahmad@gmail.com","password":"abc123","requestedRole":"clerk"}

### Role Sample Parameter

    {"role":"administrator"}
