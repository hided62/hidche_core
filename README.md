# 삼국지 모의전투 HiDCHe

삼국지 모의전투 유기체서버를 기반으로 더욱 더 진화한 서버입니다.


## 요구사항

* Apache2
  * <code>mpm_event</code> 권장
* PHP 7.2 이상 
  * <code>php-fpm</code> 권장
  * php에서 curl, pdo-sqlite을 실행가능해야 합니다. (기본값: 지원)
  * mysqlnd가 지원되어야합니다. (기본값: 지원)
* MariaDB 10.2.1 이상
  * MySQL 5.6 이상도 가능하도록 할 예정입니다.
* <code>git</code>
* <code>curl</code>

Linux는 Ubuntu 16.04, Windows는 Windows 10에서 XAMPP를 사용한 환경에서 테스트되었습니다.

Docker를 이용한 설치는 계획중입니다.

## Docker를 이용한 설치

https://storage.hided.net/gogs/devsam/docker 를 참고해 주세요.

## 수동 설치

본 게임은 <code>git</code>을 이용한 업그레이드 시스템을 구현하였으므로, <code>git</code>이 필요합니다.
또한 웹 서비스 데몬을 운영중인 사용자(일반적으로 <code>www-data</code>, <code>apache</code>)에게 디렉토리 권한이 주어져야합니다.

### 다운로드

웹 데몬 user가 <code>www-data</code>인 경우 다음과 같이 입력하여 최신 배포버전과 이미지 파일을 얻을 수 있습니다.

```
sudo -u www-data git clone https://storage.hided.net/gogs/devsam/core.git
git clone https://storage.hided.net/gogs/devsam/image.git
```

> 이미지는 git hook을 이용한 업데이트 기능을 제공하지만 아직 범용성 있는 설계가 되어있진 않습니다.

### 설치

이후 해당 경로를 웹 브라우저를 통해 접근하여 설치를 진행할 수 있습니다.

Database 수는 로그인 관리 서버 1개, 내부 서버 7개로, 총 8개의 Database가 필요합니다. 내부 설정을 고쳐서 서버 수를 늘리거나 줄일 경우 그에 맞는 Database 수가 필요합니다. 또한 Database마다 관리할 별도의 계정을 만드는 것을 추천합니다.

설치 이후에는 서버관리 페이지에서 **업데이트** 명령을 통해 원 클릭 업데이트가 가능합니다.


## 카카오로그인 연동

현재 카카오로그인 API KEY를 입력하는 작업이 설치과정에 추가되어있지 않습니다.

설치 후 <code>d_setting/KakaoKey.php</code>에서 API키를 입력해야 합니다.


## 라이선스

본 게임을 수정하거나 재배포할 경우 다음 중 하나의 라이선스를 선택하여 적용할 수 있습니다.

* MIT License
* GPL 2.0 또는 이후

만약 별도의 라이선스를 적용하고자 할 경우 Hide_D에게 문의하여 주십시오.