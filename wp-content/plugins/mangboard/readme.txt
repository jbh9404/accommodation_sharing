﻿=== Mang Board WP===
Contributors: kitae-park
Donate link: http://www.mangboard.com/donate/
Tags: board,mangboard,bbs,bulletin,gallery,image,calendar,seo,plugin,shortcode,social,korea,korean,kingkong,kboard,망보드,워드프레스게시판,한국형게시판,게시판
Requires at least: 4.0.0
Tested up to: 4.8
Stable tag: 1.4.9
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Mang Board is bulletin board (홈페이지 제작에 필요한 다양한 기능을 제공하는 한국형 게시판 플러그인입니다)

== Description ==

**Mang Board WP란??**

* Mang Board WP는 워드프레스 게시판 형태로 제공되는 플러그인으로
자료실 게시판, 갤러리(Gallery) 게시판, 캘린더(Calendar) 게시판, 회원관리, 통계관리, 쇼핑몰, 
소셜로그인, 소셜공유, 검색엔진최적화(SEO) 등의 다양한 기능을 제공합니다.

**Mang Board 특징**

* 빠르게 변화하는 기술, 플랫폼에 보다 쉽게 대응할 수 있다
* 커스터마이징을 위한 게시판으로 구조를 쉽게 변형할 수 있다
* 데스크탑, 태블릿, 모바일 등 다양한 디바이스에 맞는 반응형웹 구축이 가능하다
* 플러그인 기능을 통해 다양한 기능을 추가할 수 있다
* 다국어 기능 및 보안 인증서(SSL) 기능을 지원한다
* 다른 한국형 게시판(kboard,kingkong board)과 혼합해서 사용이 가능하다

**Mang Board 기능**

* MB-BASIC: 자료실, 갤러리, 캘린더, 문의하기, 웹진, 자주묻는질문 게시판
* MB-BUSINESS: 회원가입, 소셜 로그인, 회원정보, 회원관리, 소셜 공유, 검색 최적화 
* MB-COMMERCE: 반응형 쇼핑몰, 오픈마켓, 포인트, 쿠폰, 상품관리, 카트, 주문, 결제

**Mang Board Support**

* Homepage: [http://www.mangboard.com](http://www.mangboard.com)
* Demo: [http://demo.mangboard.com](http://demo.mangboard.com)
* Manual: [http://www.mangboard.com/manual](http://www.mangboard.com/manual)



== Installation ==

**Mang Board Installation (English)**

* Upload the entire "mangboard" folder to the "/wp-content/plugins/" directory.
* Activate the plugin through the 'Plugins' menu in WordPress.


**Mang Board Installation (Korean)**

* 플러그인 압축파일을 다운로드 받아 워드프레스 “/wp-content/plugins” 폴더에 업로드 합니다
* “/wp-content/plugins/mangboard” 폴더가 보이시면 워드프레스 설치된 플러그인 목록에 나타납니다
* 워드프레스 관리자 화면에서 “플러그인>설치된 플러그인” 목록에서 “Mang Board WP” 플러그인을 찾아 활성화 버튼을 클릭합니다
* 왼쪽에 있는 워드프레스 관리자 메뉴에서 “Mang Board” 메뉴가 보이시면 설치가 정상적으로 완료 되었습니다


== Frequently Asked Questions ==

= Mang Board 라이센스는 어떻게 되나요? =

* 상업적, 비상업적 용도에 상관없이 무료 사용 가능
* 기타 문제 발생시 GPL2 라이센스 내용 준수(http://www.gnu.org/licenses/gpl-2.0.html)

= Mang Board 설치에 필요한 서버 환경은 어떻게 되나요? =

* WordPress 4.0 or greater
* PHP version 5.4.0 or greater
* MySQL version 5.0.7 or greater

= Mang Board 게시판을 추가하려면 어떻게 해야 하나요? =

*  “Mangboard>게시판 관리” 메뉴를 클릭하고 “게시판 추가” 버튼을 클릭합니다
*  게시판 이름을 입력하고 기타 게시판 옵션들을 설정하고 “확인” 버튼을 클릭해서 게시판을 추가 합니다 ( 게시판 이름만 필수 입력 )
*  게시판 목록에 추가된 게시판의 이름과 워드프레스 페이지에 추가할 수 있는 Shortcode 가 나타납니다
*  원하는 형태의 Shortcode를 복사한 다음 관리자 메뉴 “페이지>새 페이지 추가” 메뉴를 클릭합니다
*  페이지 제목을 입력하고 복사한 Shortcode를 에디터 텍스트 영역에 복사한 다음 “공개하기” 버튼을 클릭하면 망보드 게시판이 워드프레스 페이지에 등록됩니다
*  등록된 페이지를 홈페이지 메뉴에 추가합니다

= Mang Board 회원 권한 설정은 어떻게 되나요? =

* 비회원 : Level 0
* 회원 : Level 1~10
* 관리자 : Level 10



== Screenshots == 

1. Mang Board > Board
2. Mang Board > Gallery
3. Mang Board > Calendar
4. Mang Board > Webzine
5. Mang Board > Frequently Asked Questions
6. Mang Board > Form

== Changelog ==

= 1.4.9 =
* 일부 계정에서 자동등록방지 이미지 나타나지 않는 버그 수정
* 보안 기능 및 SSL 설정 기능 수정

= 1.4.8 =
* PHP안티웹셸(카페24) 환경 지원을 위한 플러그인 구조 변경(스마트에디터,자동등록방지,파일)

= 1.4.7 =
* 게시물 바로가기 파라미터(vid) 추가 (http://홈페이지/board/?vid=글번호)
* PHP GD 라이브러리를 지원하지 않는 계정에서 썸네일을 생성하지 못해 업로드(이미지) 안되는 버그 수정
* 다국어 기능 수정

= 1.4.6 =
* 다국어 기능 개선 및 언어 추가 (영어,중국어,일본어)
* 망보드 대시보드에서 언어(영어,중국어,일본어,한국어) 변경 기능 추가
* 파일 업로드 버그 수정 (에디터 이미지 업로드 플러그인만 해당)

= 1.4.5 =
* 템플릿 확장 기능 수정
* 파일 업로드 보안 기능 추가
* 파일이름 중간에 ".php"가 있을 경우 업로드 안되도록 수정
* minor bug 수정

= 1.4.4 =
* 일부 플러그인과의 충돌문제 수정
* 기본스킨(bbs_basic) 폰트 13px로 수정
* 스마트에디터 기본 폰트 크기 10pt로 수정
* 이미지 로딩 방식 및 회원 필드 수정
