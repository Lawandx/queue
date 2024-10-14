<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ห้องวิชาการ</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Taviraj:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Taviraj', serif; 
      background-color: #333;
      color: #fff;
    }

    .slider {
      position: relative;
      width: 100%;
      max-width: 100%;
      height: 100vh;
      overflow: hidden;
    }

    .owl-carousel .item {
      position: relative;
      width: 100%;
      height: 100vh;
      background-size: cover;
      background-position: center;
    }

    .item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.5;
    }

    .caption {
        position: absolute;
        bottom: 100px;
        left: 50px;
        color: #fff;
        max-width: 100%; /* ขยายความกว้าง caption */
        width: 100%; /* ให้เต็มความกว้าง */
      }

      .caption h2 {
        font-size: 50px; /* ลดขนาดฟอนต์ลง */
        margin-bottom: 20px;
        white-space: nowrap; /* บังคับให้ข้อความอยู่ในบรรทัดเดียว */
      }

      .caption p {
        font-size: 23px;
        margin-bottom: 20px;
      }

    .btn {
      display: inline-block;
      padding: 10px 20px;
      background-color: #ff7f00;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }

    .owl-dots {
      text-align: center;
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
    }

    .owl-dot {
      display: inline-block;
      width: 15px;
      height: 15px;
      margin: 5px;
      background-color: #ff7f00;
      border-radius: 50%;
      transition: background-color 0.3s ease;
    }

    .owl-dot.active {
      background-color: #fff;
    }

    .top-right {
      position: absolute;
      top: 30px;
      right: 30px;
      z-index: 1000;
      /* ทำให้ปุ่มอยู่เหนือองค์ประกอบอื่นๆ */
    }

    .sign-in-btn {
      padding: 10px 20px;
      background-color: #ff7f00;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
      /* เพิ่มเงาให้ปุ่มเพื่อทำให้ดูเด่นขึ้น */
    }

    .sign-in-btn:hover {
      background-color: #e06b00;
      /* เปลี่ยนสีเมื่อโฮเวอร์เพื่อทำให้ดูมีการตอบสนอง */
    }

    footer {
      background-color: #222;
      color: #fff;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.3);
    }

    .footer-left {
      display: flex;
      align-items: center;
    }

    .footer-logo {
      width: 80px;
      /* ปรับขนาดโลโก้ */
      margin-right: 15px;
      /* เพิ่มช่องว่างระหว่างโลโก้กับข้อความ */
    }

    .footer-info p {
      margin: 0;
      line-height: 1.5;
      /* จัดระยะห่างบรรทัด */
    }

    .footer-address {
      text-align: right;
    }

    .footer-address p {
      margin: 5px 0;
      font-size: 14px;
      color: #aaa;
    }
    
  </style>
</head>

<body>
  <div class="slider">
    <div class="top-right">
      <a href="login.php" class="sign-in-btn">Sign In</a>
    </div>
    <div class="owl-carousel owl-theme">
      <div class="item">
        <img src="https://images.unsplash.com/photo-1707510917424-2d66055df14d?q=80&w=2071&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="สหกิจศึกษา และวิทยานิพนธ์ระดับปริญญาตรี" />
        <div class="caption">
          <h2>สหกิจศึกษา และวิทยานิพนธ์ระดับปริญญาตรี</h2>
          <p>
            การจัดการและสนับสนุนการศึกษาสหกิจศึกษา
            และวิทยานิพนธ์ระดับปริญญาตรี
            เพื่อให้มั่นใจว่านิสิตจะได้รับประสบการณ์การเรียนรู้ที่มีคุณภาพ.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
      <div class="item">
        <img src="https://images.unsplash.com/photo-1509228468518-180dd4864904?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="พัฒนาหลักสูตร" />
        <div class="caption">
          <h2>พัฒนาหลักสูตร</h2>
          <p>
            การพัฒนาและปรับปรุงหลักสูตรการศึกษาให้สอดคล้องกับความต้องการของตลาดแรงงานและความก้าวหน้าทางวิชาการในปัจจุบัน.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
      <div class="item">
        <img src="https://images.unsplash.com/photo-1686030323326-63991462052e?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="บัณฑิตศึกษา ป.โท ป.เอก" />
        <div class="caption">
          <h2>บัณฑิตศึกษา ป.โท ป.เอก</h2>
          <p>
            การจัดการและสนับสนุนการศึกษาระดับบัณฑิตศึกษา
            (ปริญญาโทและปริญญาเอก)
            เพื่อให้บัณฑิตมีความรู้และทักษะที่เหมาะสมต่อการพัฒนาประเทศ.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
      <div class="item">
        <img src="https://images.unsplash.com/photo-1550565118-3a14e8d0386f?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="บริหารนิสิตทุน" />
        <div class="caption">
          <h2>บริหารนิสิตทุน</h2>
          <p>
            การจัดการทุนการศึกษาและสนับสนุนนิสิตทุนในการศึกษาตลอดหลักสูตร
            เพื่อให้มั่นใจว่านิสิตสามารถศึกษาได้อย่างเต็มที่.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
      <div class="item">
        <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="NU งานทะเบียน" />
        <div class="caption">
          <h2>NU งานทะเบียน</h2>
          <p>
            การจัดการและบริการงานทะเบียนสำหรับนิสิต รวมถึงการรับสมัคร
            จัดการข้อมูลนิสิต และการออกเอกสารทางการศึกษา.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
      <div class="item">
        <img src="https://images.pexels.com/photos/256541/pexels-photo-256541.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="การจัดการเรียนการสอน" />
        <div class="caption">
          <h2>การจัดการเรียนการสอน</h2>
          <p>
            การวางแผนและบริหารจัดการการเรียนการสอนในห้องเรียน
            เพื่อให้การเรียนการสอนเป็นไปอย่างมีประสิทธิภาพและบรรลุผลตามเป้าหมาย.
          </p>
          <a href="queue_page.php" class="btn">จองคิวได้ที่นี่ !!</a>
        </div>
      </div>
    </div>
  </div>
  <footer>
      <div class="footer-left">
        <img src="nu.png" alt="NU Logo" class="footer-logo" />
        <div class="footer-info">
          <p>มหาวิทยาลัยนเรศวร</p>
          <p>Naresuan University</p>
        </div>
      </div>
      <div class="footer-address">
        <p>ที่อยู่: 99 หมู่ 9 ตำบล ท่าโพธิ์ อำเภอเมือง จังหวัด พิษณุโลก 65000</p>
        <p>โทรศัพท์: 055-963112 | โทรสาร: 055-963113</p>
        <p>Email: saraban_sci@nu.ac.th</p>
      </div>
    </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script>
    $(document).ready(function() {
      $(".owl-carousel").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        nav: false,
        dots: true,
      });
    });
  </script>
</body>

</html>