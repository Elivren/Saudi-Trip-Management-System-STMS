// =============================================
// STMS - Saudi Trip Management System
// Main JavaScript Application
// =============================================

// ===== Navbar Scroll Effect =====
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// ===== Mobile Menu Toggle =====
function toggleMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) {
        navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
    }
}

// ===== Sidebar Toggle (Dashboard) =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// ===== Language / Translation System =====
const translations = {
    // ---- Navbar & Common ----
    nav_home: { en: 'Home', ar: 'الرئيسية' },
    nav_back_home: { en: 'Back to Home', ar: 'العودة للرئيسية' },
    nav_about: { en: 'About', ar: 'عن النظام' },
    nav_contact: { en: 'Contact', ar: 'تواصل معنا' },
    nav_faq: { en: 'FAQ', ar: 'الأسئلة الشائعة' },
    nav_browse: { en: 'Browse Trips', ar: 'تصفح الرحلات' },
    nav_login: { en: 'Login', ar: 'تسجيل الدخول' },
    nav_back_trips: { en: 'Back to Trips', ar: 'العودة للرحلات' },
    footer_copy: { en: '© 2026 STMS - Saudi Trip Management System. All rights reserved.', ar: '© 2026 STMS - نظام إدارة الرحلات السعودية. جميع الحقوق محفوظة.' },
    footer_quick: { en: 'Quick Links', ar: 'روابط سريعة' },
    footer_dest: { en: 'Destinations', ar: 'الوجهات' },
    footer_support: { en: 'Support', ar: 'الدعم' },
    footer_about: { en: 'About Us', ar: 'عن النظام' },
    footer_login: { en: 'Login', ar: 'تسجيل الدخول' },
    footer_register: { en: 'Register', ar: 'إنشاء حساب' },
    footer_faq: { en: 'FAQ', ar: 'الأسئلة الشائعة' },
    footer_contact_us: { en: 'Contact Us', ar: 'تواصل معنا' },
    footer_terms: { en: 'Terms of Service', ar: 'شروط الخدمة' },
    footer_privacy: { en: 'Privacy Policy', ar: 'سياسة الخصوصية' },
    footer_desc: { en: 'Saudi Trip Management System - Your gateway to unforgettable experiences across the Kingdom of Saudi Arabia.', ar: 'نظام إدارة الرحلات السعودية - بوابتك لتجارب لا تُنسى عبر المملكة العربية السعودية.' },

    // ---- Index / Hero ----
    hero_badge: { en: 'Discover the Beauty of Saudi Arabia', ar: 'اكتشف جمال المملكة العربية السعودية' },
    hero_title_1: { en: 'Saudi', ar: 'نظام إدارة' },
    hero_title_2: { en: 'Trip', ar: 'الرحلات' },
    hero_title_3: { en: 'Management System', ar: 'السعودية' },
    hero_subtitle: { en: 'Explore breathtaking destinations across Saudi Arabia. From ancient heritage sites to modern marvels, your perfect adventure awaits.', ar: 'استكشف وجهات خلابة عبر المملكة العربية السعودية. من المواقع التراثية القديمة إلى العجائب الحديثة، مغامرتك المثالية في انتظارك.' },
    hero_welcome: { en: 'Welcome to', ar: 'مرحبًا بك في' },
    hero_gateway: { en: 'Your gateway to unforgettable Saudi experiences', ar: 'بوابتك لتجارب سعودية لا تُنسى' },
    hero_browse: { en: 'Browse Trips', ar: 'تصفح الرحلات' },
    hero_login: { en: 'Login / Register', ar: 'دخول / تسجيل' },
    hero_desert: { en: 'Desert & Mountains', ar: 'صحراء وجبال' },
    hero_sea: { en: 'Red Sea Coast', ar: 'ساحل البحر الأحمر' },
    hero_heritage: { en: 'Heritage Sites', ar: 'مواقع تراثية' },
    hero_cities: { en: 'Modern Cities', ar: 'مدن حديثة' },

    // ---- About ----
    about_title: { en: 'About STMS', ar: 'عن نظام STMS' },
    about_desc: { en: 'Saudi Trip Management System connects tourists with certified local guides for authentic Saudi Arabian travel experiences.', ar: 'نظام إدارة الرحلات السعودية يربط السياح بمرشدين محليين معتمدين لتجارب سفر سعودية أصيلة.' },
    about_discover: { en: 'Discover Trips', ar: 'اكتشف الرحلات' },
    about_discover_desc: { en: 'Browse hundreds of curated trips across all regions of Saudi Arabia, from desert safaris to coastal getaways.', ar: 'تصفح مئات الرحلات المختارة عبر جميع مناطق المملكة العربية السعودية.' },
    about_guides: { en: 'Expert Guides', ar: 'مرشدون خبراء' },
    about_guides_desc: { en: 'Connect with certified local guides who bring deep knowledge and passion for Saudi culture and heritage.', ar: 'تواصل مع مرشدين محليين معتمدين يتمتعون بمعرفة عميقة وشغف بالثقافة والتراث السعودي.' },
    about_safe: { en: 'Safe & Secure', ar: 'آمن وموثوق' },
    about_safe_desc: { en: 'Book with confidence. All guides are verified and trips are monitored to ensure your safety and satisfaction.', ar: 'احجز بثقة. جميع المرشدين معتمدون والرحلات مراقبة لضمان سلامتك ورضاك.' },

    // ---- Contact ----
    contact_title: { en: 'Get in Touch', ar: 'تواصل معنا' },
    contact_desc: { en: "Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.", ar: 'هل لديك أسئلة؟ يسعدنا سماعك. أرسل لنا رسالة وسنرد في أقرب وقت ممكن.' },
    contact_email: { en: 'Email', ar: 'البريد الإلكتروني' },
    contact_phone: { en: 'Phone', ar: 'الهاتف' },
    contact_address: { en: 'Address', ar: 'العنوان' },
    contact_address_val: { en: 'Riyadh, Saudi Arabia', ar: 'الرياض، المملكة العربية السعودية' },
    contact_name: { en: 'Your Name', ar: 'اسمك' },
    contact_email_label: { en: 'Email Address', ar: 'البريد الإلكتروني' },
    contact_subject: { en: 'Subject', ar: 'الموضوع' },
    contact_message: { en: 'Message', ar: 'الرسالة' },
    contact_send: { en: 'Send Message', ar: 'إرسال الرسالة' },
    contact_success_title: { en: 'Message Sent!', ar: 'تم إرسال الرسالة!' },
    contact_success_desc: { en: "Thank you for reaching out. We'll get back to you soon.", ar: 'شكراً لتواصلك. سنعود إليك قريباً.' },
    contact_name_ph: { en: 'Enter your name', ar: 'أدخل اسمك' },
    contact_email_ph: { en: 'Enter your email', ar: 'أدخل بريدك الإلكتروني' },
    contact_subject_ph: { en: 'Subject (optional)', ar: 'الموضوع (اختياري)' },
    contact_message_ph: { en: 'Write your message...', ar: 'اكتب رسالتك...' },

    // ---- FAQ ----
    faq_title: { en: 'Frequently Asked Questions', ar: 'الأسئلة الشائعة' },
    faq_desc: { en: 'Find answers to common questions about STMS', ar: 'اعثر على إجابات للأسئلة الشائعة حول STMS' },

    // ---- Login ----
    login_explore: { en: 'Explore Saudi Arabia', ar: 'استكشف المملكة العربية السعودية' },
    login_explore_desc: { en: 'Login to access your personalized dashboard, manage bookings, and discover amazing trips across the Kingdom.', ar: 'سجل دخولك للوصول إلى لوحة التحكم الخاصة بك وإدارة حجوزاتك واكتشاف رحلات مذهلة عبر المملكة.' },
    login_welcome: { en: 'Welcome Back', ar: 'مرحبًا بعودتك' },
    login_subtitle: { en: 'Sign in to your account to continue', ar: 'سجل دخولك إلى حسابك للمتابعة' },
    login_tourist: { en: 'Tourist', ar: 'سائح' },
    login_guide: { en: 'Guide', ar: 'مرشد' },
    login_admin: { en: 'Admin', ar: 'مدير' },
    login_email: { en: 'Email Address', ar: 'البريد الإلكتروني' },
    login_password: { en: 'Password', ar: 'كلمة المرور' },
    login_btn: { en: 'Login', ar: 'تسجيل الدخول' },
    login_no_account: { en: "Don't have an account?", ar: 'ليس لديك حساب؟' },
    login_create: { en: 'Create new account', ar: 'إنشاء حساب جديد' },
    login_or: { en: 'or', ar: 'أو' },
    login_back_home: { en: 'Back to Home', ar: 'العودة للرئيسية' },
    login_email_ph: { en: 'Enter your email', ar: 'أدخل بريدك الإلكتروني' },
    login_pass_ph: { en: 'Enter your password', ar: 'أدخل كلمة المرور' },

    // ---- Register ----
    reg_join: { en: 'Join Our Community', ar: 'انضم إلى مجتمعنا' },
    reg_join_desc: { en: 'Create an account to start booking amazing trips across Saudi Arabia, or register as a guide to share your expertise with travelers from around the world.', ar: 'أنشئ حسابًا لبدء حجز رحلات مذهلة عبر المملكة العربية السعودية، أو سجل كمرشد لمشاركة خبرتك مع المسافرين من جميع أنحاء العالم.' },
    reg_feat1: { en: 'Access to hundreds of curated trips', ar: 'الوصول إلى مئات الرحلات المختارة' },
    reg_feat2: { en: 'Connect with certified local guides', ar: 'تواصل مع مرشدين محليين معتمدين' },
    reg_feat3: { en: 'Secure booking and payment system', ar: 'نظام حجز ودفع آمن' },
    reg_feat4: { en: 'Rate and review your experiences', ar: 'قيّم وراجع تجاربك' },
    reg_title: { en: 'Create Account', ar: 'إنشاء حساب' },
    reg_subtitle: { en: 'Join STMS and start your Saudi adventure', ar: 'انضم إلى STMS وابدأ مغامرتك السعودية' },
    reg_name: { en: 'Full Name', ar: 'الاسم الكامل' },
    reg_email: { en: 'Email Address', ar: 'البريد الإلكتروني' },
    reg_phone: { en: 'Phone Number', ar: 'رقم الهاتف' },
    reg_password: { en: 'Password', ar: 'كلمة المرور' },
    reg_confirm: { en: 'Confirm Password', ar: 'تأكيد كلمة المرور' },
    reg_join_as: { en: 'I want to join as', ar: 'أريد الانضمام كـ' },
    reg_tourist: { en: 'Tourist', ar: 'سائح' },
    reg_guide: { en: 'Guide', ar: 'مرشد' },
    reg_city: { en: 'City', ar: 'المدينة' },
    reg_region: { en: 'Region', ar: 'المنطقة' },
    reg_optional: { en: '(Optional)', ar: '(اختياري)' },
    reg_btn: { en: 'Register', ar: 'تسجيل' },
    reg_has_account: { en: 'Already have an account?', ar: 'لديك حساب بالفعل؟' },
    reg_back_login: { en: 'Back to Login', ar: 'العودة لتسجيل الدخول' },
    reg_name_ph: { en: 'Enter your full name', ar: 'أدخل اسمك الكامل' },
    reg_email_ph: { en: 'Enter your email', ar: 'أدخل بريدك الإلكتروني' },
    reg_pass_ph: { en: 'Min 6 characters', ar: 'الحد الأدنى 6 أحرف' },
    reg_confirm_ph: { en: 'Confirm password', ar: 'تأكيد كلمة المرور' },
    select_city: { en: 'Select City', ar: 'اختر المدينة' },
    select_region: { en: 'Select Region', ar: 'اختر المنطقة' },

    // ---- Trips Page ----
    trips_title: { en: 'Explore Saudi Trips', ar: 'استكشف الرحلات السعودية' },
    trips_subtitle: { en: 'Discover amazing adventures across the Kingdom', ar: 'اكتشف مغامرات مذهلة عبر المملكة' },
    trips_welcome: { en: 'Welcome, ', ar: 'مرحبًا، ' },
    trips_navigation: { en: 'Navigation', ar: 'التنقل' },
    trips_browse: { en: 'Browse Trips', ar: 'تصفح الرحلات' },
    trips_home: { en: 'Home', ar: 'الرئيسية' },
    trips_my_account: { en: 'My Account', ar: 'حسابي' },
    trips_my_bookings: { en: 'My Bookings', ar: 'حجوزاتي' },
    trips_my_reviews: { en: 'My Reviews', ar: 'تقييماتي' },
    trips_profile: { en: 'Profile', ar: 'الملف الشخصي' },
    trips_quick_links: { en: 'Quick Links', ar: 'روابط سريعة' },
    trips_about: { en: 'About', ar: 'عن النظام' },
    trips_contact: { en: 'Contact', ar: 'تواصل معنا' },
    trips_logout: { en: 'Logout', ar: 'تسجيل الخروج' },
    trips_login_reg: { en: 'Login / Register', ar: 'دخول / تسجيل' },
    filter_location: { en: 'Location', ar: 'الموقع' },
    filter_all: { en: 'All Locations', ar: 'جميع المواقع' },
    filter_date: { en: 'Date From', ar: 'التاريخ من' },
    filter_price: { en: 'Max Price (SAR)', ar: 'أقصى سعر (ريال)' },
    filter_any: { en: 'Any Price', ar: 'أي سعر' },
    filter_under: { en: 'Under', ar: 'أقل من' },
    filter_search: { en: 'Search', ar: 'بحث' },
    filter_search_ph: { en: 'Search trips...', ar: 'ابحث عن رحلات...' },
    trips_view_details: { en: 'View Details', ar: 'عرض التفاصيل' },
    trips_no_found: { en: 'No trips found', ar: 'لم يتم العثور على رحلات' },
    trips_adjust: { en: 'Try adjusting your filters', ar: 'حاول تعديل عوامل التصفية' },
    trips_loading: { en: 'Loading trips...', ar: 'جاري تحميل الرحلات...' },
    bookings_title: { en: 'My Bookings', ar: 'حجوزاتي' },
    bookings_subtitle: { en: 'Your trip reservations', ar: 'حجوزات رحلاتك' },
    bookings_back: { en: 'Back to Trips', ar: 'العودة للرحلات' },
    reviews_title: { en: 'My Reviews', ar: 'تقييماتي' },
    reviews_subtitle: { en: 'Reviews you have written', ar: 'التقييمات التي كتبتها' },

    // ---- Trip Details ----
    td_about: { en: 'About This Trip', ar: 'عن هذه الرحلة' },
    td_itinerary: { en: 'Itinerary', ar: 'برنامج الرحلة' },
    td_guide: { en: 'Your Guide', ar: 'مرشدك' },
    td_reviews: { en: 'Reviews', ar: 'التقييمات' },
    td_per_person: { en: 'per person', ar: 'للشخص' },
    td_duration: { en: 'Duration', ar: 'المدة' },
    td_spots: { en: 'Spots Left', ar: 'أماكن متاحة' },
    td_book: { en: 'Book This Trip', ar: 'احجز هذه الرحلة' },
    td_select_date: { en: 'Select Date', ar: 'اختر التاريخ' },
    td_num_people: { en: 'Number of People', ar: 'عدد الأشخاص' },
    td_person: { en: 'Person', ar: 'شخص' },
    td_people: { en: 'People', ar: 'أشخاص' },
    td_notes: { en: 'Special Notes', ar: 'ملاحظات خاصة' },
    td_notes_opt: { en: '(Optional)', ar: '(اختياري)' },
    td_notes_ph: { en: 'Any special requests...', ar: 'أي طلبات خاصة...' },
    td_price_x: { en: 'Price x', ar: 'السعر ×' },
    td_book_btn: { en: 'Book Trip', ar: 'احجز الرحلة' },
    td_booked_title: { en: 'Booking Confirmed!', ar: 'تم تأكيد الحجز!' },
    td_booked_desc: { en: 'Your booking has been confirmed! You can view it in My Bookings. Note: Cancellation is free up to 24 hours before the trip.', ar: 'تم تأكيد حجزك! يمكنك مشاهدته في حجوزاتي. ملاحظة: الإلغاء مجاني قبل 24 ساعة من موعد الرحلة.' },
    td_login_prompt: { en: 'Please login to book this trip', ar: 'يرجى تسجيل الدخول لحجز هذه الرحلة' },
    td_login_btn: { en: 'Login to Book', ar: 'سجل الدخول للحجز' },
    td_day: { en: 'Day', ar: 'اليوم' },
    td_reviews_label: { en: 'reviews', ar: 'تقييمات' },
    td_no_reviews: { en: 'No reviews yet.', ar: 'لا توجد تقييمات بعد.' },
    td_itin_soon: { en: 'Itinerary details coming soon.', ar: 'تفاصيل البرنامج قريبًا.' },

    // ---- Guide Dashboard ----
    gd_panel: { en: 'Guide Panel', ar: 'لوحة المرشد' },
    gd_my_trips: { en: 'My Trips', ar: 'رحلاتي' },
    gd_bookings: { en: 'Bookings', ar: 'الحجوزات' },
    gd_add_trip: { en: 'Add New Trip', ar: 'إضافة رحلة جديدة' },
    gd_account: { en: 'Account', ar: 'الحساب' },
    gd_profile: { en: 'Profile', ar: 'الملف الشخصي' },
    gd_reviews: { en: 'Client Reviews', ar: 'تقييمات العملاء' },
    gd_quick_links: { en: 'Quick Links', ar: 'روابط سريعة' },
    gd_home: { en: 'Home', ar: 'الرئيسية' },
    gd_logout: { en: 'Logout', ar: 'تسجيل الخروج' },
    gd_welcome: { en: 'Welcome, ', ar: 'مرحبًا، ' },
    gd_manage_trips: { en: 'Manage your trips and bookings', ar: 'إدارة رحلاتك وحجوزاتك' },
    gd_manage_bookings: { en: 'Manage tourist bookings for your trips', ar: 'إدارة حجوزات السياح لرحلاتك' },
    gd_create_desc: { en: 'Create a new trip experience', ar: 'إنشاء تجربة رحلة جديدة' },
    gd_reviews_desc: { en: 'See what tourists say about your trips', ar: 'اطلع على ما يقوله السياح عن رحلاتك' },
    gd_trip_title: { en: 'Trip Title', ar: 'عنوان الرحلة' },
    gd_location: { en: 'Location', ar: 'الموقع' },
    gd_date: { en: 'Date', ar: 'التاريخ' },
    gd_price: { en: 'Price', ar: 'السعر' },
    gd_status: { en: 'Status', ar: 'الحالة' },
    gd_bookings_col: { en: 'Bookings', ar: 'الحجوزات' },
    gd_actions: { en: 'Actions', ar: 'الإجراءات' },
    gd_no_trips: { en: 'No trips yet. Create your first trip!', ar: 'لا توجد رحلات بعد. أنشئ رحلتك الأولى!' },
    gd_trip_bookings: { en: 'Trip Bookings', ar: 'حجوزات الرحلة' },
    gd_select_trip: { en: 'Select a trip', ar: 'اختر رحلة' },
    gd_tourist: { en: 'Tourist', ar: 'السائح' },
    gd_email: { en: 'Email', ar: 'البريد' },
    gd_phone: { en: 'Phone', ar: 'الهاتف' },
    gd_people: { en: 'People', ar: 'عدد' },
    gd_no_bookings: { en: 'No bookings for this trip', ar: 'لا توجد حجوزات لهذه الرحلة' },
    gd_select_above: { en: 'Select a trip above to view its bookings', ar: 'اختر رحلة أعلاه لعرض حجوزاتها' },
    gd_accept: { en: 'Accept', ar: 'قبول' },
    gd_reject: { en: 'Reject', ar: 'رفض' },
    gd_create_trip: { en: 'Create New Trip', ar: 'إنشاء رحلة جديدة' },
    gd_edit_trip: { en: 'Edit Trip', ar: 'تعديل الرحلة' },
    gd_title_label: { en: 'Trip Title', ar: 'عنوان الرحلة' },
    gd_description: { en: 'Description', ar: 'الوصف' },
    gd_city: { en: 'City', ar: 'المدينة' },
    gd_region: { en: 'Region', ar: 'المنطقة' },
    gd_cover_image: { en: 'Cover Image URL', ar: 'رابط صورة الغلاف' },
    gd_price_label: { en: 'Price (SAR)', ar: 'السعر (ريال)' },
    gd_max_tourists: { en: 'Max Tourists', ar: 'الحد الأقصى' },
    gd_start_date: { en: 'Start Date', ar: 'تاريخ البدء' },
    gd_end_date: { en: 'End Date', ar: 'تاريخ الانتهاء' },
    gd_duration_label: { en: 'Duration', ar: 'المدة' },
    gd_itinerary: { en: 'Itinerary', ar: 'البرنامج' },
    gd_add_day: { en: 'Add Day', ar: 'إضافة يوم' },
    gd_cancel: { en: 'Cancel', ar: 'إلغاء' },
    gd_save_create: { en: 'Create Trip', ar: 'إنشاء الرحلة' },
    gd_save_update: { en: 'Update Trip', ar: 'تحديث الرحلة' },
    gd_confirm_delete: { en: 'Confirm Deletion', ar: 'تأكيد الحذف' },
    gd_delete_msg: { en: 'Are you sure you want to delete this trip? This action cannot be undone.', ar: 'هل أنت متأكد من حذف هذه الرحلة؟ لا يمكن التراجع عن هذا الإجراء.' },
    gd_delete: { en: 'Delete Trip', ar: 'حذف الرحلة' },
    gd_no_reviews: { en: 'No reviews yet.', ar: 'لا توجد تقييمات بعد.' },
    gd_status_open: { en: 'Open', ar: 'مفتوح' },
    gd_status_booked: { en: 'Fully Booked', ar: 'مكتمل' },
    gd_status_completed: { en: 'Completed', ar: 'مكتمل' },
    gd_status_cancelled: { en: 'Cancelled', ar: 'ملغى' },

    // ---- Admin Dashboard ----
    ad_panel: { en: 'Admin Panel', ar: 'لوحة المدير' },
    ad_dashboard: { en: 'Dashboard', ar: 'لوحة التحكم' },
    ad_users: { en: 'Users', ar: 'المستخدمون' },
    ad_trips: { en: 'Trips', ar: 'الرحلات' },
    ad_bookings: { en: 'Bookings', ar: 'الحجوزات' },
    ad_reviews: { en: 'Reviews', ar: 'التقييمات' },
    ad_faqs: { en: 'FAQs', ar: 'الأسئلة الشائعة' },
    ad_feedback: { en: 'Feedback', ar: 'الملاحظات' },
    ad_home: { en: 'Home', ar: 'الرئيسية' },
    ad_logout: { en: 'Logout', ar: 'تسجيل الخروج' },
    ad_overview: { en: 'System Overview', ar: 'نظرة عامة' },
    ad_total_users: { en: 'Total Users', ar: 'إجمالي المستخدمين' },
    ad_total_trips: { en: 'Total Trips', ar: 'إجمالي الرحلات' },
    ad_total_bookings: { en: 'Total Bookings', ar: 'إجمالي الحجوزات' },
    ad_total_reviews: { en: 'Total Reviews', ar: 'إجمالي التقييمات' },

    // ---- Booking Page ----
    bk_title: { en: 'Book Your Trip', ar: 'احجز رحلتك' },
    bk_subtitle: { en: 'Choose how you want to plan your Saudi adventure', ar: 'اختر كيف تريد التخطيط لمغامرتك السعودية' },
    bk_self_planned: { en: 'Self-Planned Booking', ar: 'حجز ذاتي التخطيط' },
    bk_self_desc: { en: 'Plan your own itinerary. Choose your destinations, set the duration, and customize your trip.', ar: 'خطط لبرنامجك الخاص. اختر وجهاتك، حدد المدة، وخصص رحلتك.' },
    bk_package: { en: 'Pre-Designed Package', ar: 'باقة جاهزة' },
    bk_package_desc: { en: 'Choose from expertly crafted packages by certified tour guides.', ar: 'اختر من باقات مصممة بعناية من مرشدين معتمدين.' },
    bk_choose_dest: { en: 'Choose Destinations', ar: 'اختر الوجهات' },
    bk_total_days: { en: 'Total Days', ar: 'إجمالي الأيام' },
    bk_add_dest: { en: 'Add Destination', ar: 'إضافة وجهة' },
    bk_extension: { en: 'Need More Time?', ar: 'تحتاج وقتاً أكثر؟' },
    bk_ext_desc: { en: 'The maximum trip duration is 10 days. You can add extension days if needed.', ar: 'الحد الأقصى لمدة الرحلة 10 أيام. يمكنك إضافة أيام تمديد إذا لزم الأمر.' },
    bk_ext_days: { en: 'Extension Days', ar: 'أيام التمديد' },
    bk_trip_details: { en: 'Trip Details', ar: 'تفاصيل الرحلة' },
    bk_start_date: { en: 'Start Date', ar: 'تاريخ البدء' },
    bk_num_people: { en: 'Number of People', ar: 'عدد الأشخاص' },
    bk_addons: { en: 'Optional Add-ons', ar: 'إضافات اختيارية' },
    bk_transport_incl: { en: 'Transportation is included by default', ar: 'النقل مشمول افتراضياً' },
    bk_accommodation: { en: 'Accommodation', ar: 'الإقامة' },
    bk_breakfast: { en: 'Breakfast', ar: 'الإفطار' },
    bk_lunch: { en: 'Lunch', ar: 'الغداء' },
    bk_dinner: { en: 'Dinner', ar: 'العشاء' },
    bk_price_summary: { en: 'Price Summary', ar: 'ملخص السعر' },
    bk_confirm: { en: 'Confirm Booking', ar: 'تأكيد الحجز' },
    bk_confirm_pkg: { en: 'Confirm Package Booking', ar: 'تأكيد حجز الباقة' },
    bk_choose_pkg: { en: 'Choose a Package', ar: 'اختر باقة' },
    bk_tour_packages: { en: 'Tour Packages', ar: 'باقات السفر' },
    bk_book_now: { en: 'Book Now', ar: 'احجز الآن' },
    bk_fixed_duration: { en: 'Fixed Duration', ar: 'مدة ثابتة' },
    bk_cannot_extend: { en: 'Cannot be extended', ar: 'لا يمكن تمديدها' },
    bk_per_person: { en: 'per person (base price)', ar: 'للشخص (السعر الأساسي)' },
    bk_flexibility: { en: 'Full flexibility', ar: 'مرونة كاملة' },
    bk_dynamic_pricing: { en: 'Dynamic city pricing', ar: 'تسعير ديناميكي حسب المدينة' },
    bk_ext_available: { en: 'Extension available', ar: 'التمديد متاح' },
    bk_expert_guide: { en: 'Expert guide included', ar: 'مرشد خبير مشمول' },
    bk_multi_city: { en: 'Multi-city itineraries', ar: 'برامج متعددة المدن' },
    bk_transport: { en: 'Transportation included', ar: 'النقل مشمول' },
    bk_back_options: { en: 'Back to Options', ar: 'العودة للخيارات' },
    book_trip_nav: { en: 'Book a Trip', ar: 'احجز رحلة' },
    ad_col_name: { en: 'Name', ar: 'الاسم' },
    ad_col_email: { en: 'Email', ar: 'البريد الإلكتروني' },
    ad_col_phone: { en: 'Phone', ar: 'الهاتف' },
    ad_col_role: { en: 'Role', ar: 'الدور' },
    ad_col_city: { en: 'City', ar: 'المدينة' },
    ad_col_status: { en: 'Status', ar: 'الحالة' },
    ad_col_joined: { en: 'Joined', ar: 'تاريخ الانضمام' },
    ad_col_actions: { en: 'Actions', ar: 'الإجراءات' },
    ad_col_title: { en: 'Title', ar: 'العنوان' },
    ad_col_guide: { en: 'Guide', ar: 'المرشد' },
    ad_col_location: { en: 'Location', ar: 'الموقع' },
    ad_col_date: { en: 'Date', ar: 'التاريخ' },
    ad_col_price: { en: 'Price', ar: 'السعر' },
    ad_col_bookings: { en: 'Bookings', ar: 'الحجوزات' },
    ad_user_breakdown: { en: 'User Breakdown', ar: 'توزيع المستخدمين' },
    ad_quick_actions: { en: 'Quick Actions', ar: 'إجراءات سريعة' },
    ad_filter_role: { en: 'Filter by Role', ar: 'تصفية حسب الدور' },
    ad_no_users: { en: 'No users found', ar: 'لا يوجد مستخدمون' },
    ad_no_trips_found: { en: 'No trips found', ar: 'لا توجد رحلات' },
    ad_no_bookings_found: { en: 'No bookings found', ar: 'لا توجد حجوزات' },
    ad_no_reviews: { en: 'No reviews', ar: 'لا توجد تقييمات' },
    ad_no_feedback: { en: 'No feedback', ar: 'لا توجد ملاحظات' },
    ad_no_faqs: { en: 'No FAQs yet. Add your first FAQ.', ar: 'لا توجد أسئلة بعد. أضف أول سؤال.' },
    ad_edit_faq: { en: 'Edit FAQ', ar: 'تعديل السؤال' },
    ad_add_faq: { en: 'Add FAQ', ar: 'إضافة سؤال' },
    privacy_title: { en: 'Privacy Policy', ar: 'سياسة الخصوصية' },
    bk_refund_notice: { en: 'You can cancel up to 24 hours before the trip for a full refund. After that, no refund will be issued.', ar: 'يمكنك الإلغاء قبل 24 ساعة من الرحلة لاسترداد كامل المبلغ. بعد ذلك لا يمكن استرداد المبلغ.' },
    bk_review_title: { en: 'Rate This Trip', ar: 'قيّم هذه الرحلة' },
    bk_review_comment: { en: 'Your Comment (Optional)', ar: 'تعليقك (اختياري)' },
    bk_review_submit: { en: 'Submit Review', ar: 'إرسال التقييم' },
    bk_review_success: { en: 'Review submitted successfully!', ar: 'تم إرسال تقييمك بنجاح!' },
    terms_title: { en: 'Terms of Service', ar: 'شروط الخدمة' },
    bk_optional_addons: { en: 'Optional Add-ons', ar: 'إضافات اختيارية' },

    // ---- Error / Validation Messages ----
    err_fill_fields: { en: 'Please fill in all fields', ar: 'يرجى ملء جميع الحقول' },
    err_fill_required: { en: 'Please fill in all required fields', ar: 'يرجى ملء جميع الحقول المطلوبة' },
    err_connection: { en: 'Connection error. Please try again.', ar: 'خطأ في الاتصال. يرجى المحاولة مرة أخرى.' },
    err_name_letters: { en: 'Full name must contain letters only, no numbers', ar: 'يجب أن يحتوي الاسم الكامل على حروف فقط، بدون أرقام' },
    err_phone_numbers: { en: 'Phone number must contain numbers only', ar: 'يجب أن يحتوي رقم الهاتف على أرقام فقط' },
    err_pass_length: { en: 'Password must be at least 6 characters', ar: 'يجب أن تكون كلمة المرور 6 أحرف على الأقل' },
    err_pass_match: { en: 'Passwords do not match', ar: 'كلمات المرور غير متطابقة' },
    err_reg_success: { en: 'Registration successful! Please login with your credentials.', ar: 'تم التسجيل بنجاح! يرجى تسجيل الدخول باستخدام بياناتك.' },
    err_min_city: { en: 'Please add at least one city', ar: 'يرجى إضافة مدينة واحدة على الأقل' },
    err_refresh: { en: 'Connection error. Please refresh.', ar: 'خطأ في الاتصال. يرجى تحديث الصفحة.' },
    err_load_packages: { en: 'Could not load packages.', ar: 'تعذّر تحميل الباقات.' },
    err_loading_packages: { en: 'Loading packages...', ar: 'جاري تحميل الباقات...' },
    err_load_trips: { en: 'Could not load trips. Please check your connection and refresh.', ar: 'تعذّر تحميل الرحلات. يرجى التحقق من اتصالك وتحديث الصفحة.' },
    err_load_pkg_bookings: { en: 'Could not load package bookings', ar: 'تعذّر تحميل حجوزات الباقات' },
    login_logging_in: { en: 'Logging in...', ar: 'جاري تسجيل الدخول...' },
    reg_creating: { en: 'Creating account...', ar: 'جاري إنشاء الحساب...' },
    gd_create_pkg: { en: 'Create New Package', ar: 'إنشاء باقة جديدة' },
    gd_edit_pkg: { en: 'Edit Package', ar: 'تعديل الباقة' },
    gd_add_pkg_btn: { en: 'Create New Package', ar: 'إنشاء باقة جديدة' },
    pf_client_reviews_label: { en: 'Client Reviews', ar: 'تقييمات العملاء' },
    bk_self_planned_title: { en: 'Self-Planned Booking', ar: 'حجز ذاتي التخطيط' },
    bk_self_planned_sub: { en: 'Design your own Saudi adventure', ar: 'صمّم مغامرتك السعودية بنفسك' },
    bk_choose_pkg_title: { en: 'Choose a Package', ar: 'اختر باقة' },
    bk_choose_pkg_sub: { en: 'Select from expertly designed tour packages', ar: 'اختر من باقات سياحية مصممة باحترافية' },
    bk_main_title: { en: 'Book Your Trip', ar: 'احجز رحلتك' },
    bk_main_sub: { en: 'Choose how you want to plan your Saudi adventure', ar: 'اختر كيف تريد التخطيط لمغامرتك السعودية' },

    // ---- Profile ----
    pf_title: { en: 'My Profile', ar: 'ملفي الشخصي' },
    pf_subtitle: { en: 'Manage your account information', ar: 'إدارة معلومات حسابك' },
    pf_dashboard: { en: 'Dashboard', ar: 'لوحة التحكم' },
    pf_profile: { en: 'Profile', ar: 'الملف الشخصي' },
    pf_home: { en: 'Home', ar: 'الرئيسية' },
    pf_browse: { en: 'Browse Trips', ar: 'تصفح الرحلات' },
    pf_logout: { en: 'Logout', ar: 'تسجيل الخروج' },
    pf_navigation: { en: 'Navigation', ar: 'التنقل' },
    pf_quick_links: { en: 'Quick Links', ar: 'روابط سريعة' },
    pf_info: { en: 'Personal Info', ar: 'المعلومات الشخصية' },
    pf_change_pw: { en: 'Change Password', ar: 'تغيير كلمة المرور' },
    pf_my_reviews: { en: 'My Reviews', ar: 'تقييماتي' },
    pf_client_reviews: { en: 'Client Reviews', ar: 'تقييمات العملاء' },
    pf_personal: { en: 'Personal Information', ar: 'المعلومات الشخصية' },
    pf_name: { en: 'Full Name', ar: 'الاسم الكامل' },
    pf_email: { en: 'Email Address', ar: 'البريد الإلكتروني' },
    pf_phone: { en: 'Phone Number', ar: 'رقم الهاتف' },
    pf_role: { en: 'Role', ar: 'الدور' },
    pf_city: { en: 'City', ar: 'المدينة' },
    pf_region: { en: 'Region', ar: 'المنطقة' },
    pf_save: { en: 'Save Changes', ar: 'حفظ التغييرات' },
    pf_updated: { en: 'Profile Updated!', ar: 'تم تحديث الملف!' },
    pf_updated_desc: { en: 'Your information has been saved successfully.', ar: 'تم حفظ معلوماتك بنجاح.' },
    pf_pw_title: { en: 'Change Password', ar: 'تغيير كلمة المرور' },
    pf_current_pw: { en: 'Current Password', ar: 'كلمة المرور الحالية' },
    pf_new_pw: { en: 'New Password', ar: 'كلمة المرور الجديدة' },
    pf_confirm_pw: { en: 'Confirm New Password', ar: 'تأكيد كلمة المرور الجديدة' },
    pf_update_pw: { en: 'Update Password', ar: 'تحديث كلمة المرور' },
    pf_pw_changed: { en: 'Password Changed!', ar: 'تم تغيير كلمة المرور!' },
    pf_pw_changed_desc: { en: 'Your password has been updated successfully.', ar: 'تم تحديث كلمة المرور بنجاح.' },
    pf_current_ph: { en: 'Enter current password', ar: 'أدخل كلمة المرور الحالية' },
    pf_new_ph: { en: 'Min 6 characters', ar: 'الحد الأدنى 6 أحرف' },
    pf_confirm_ph: { en: 'Confirm new password', ar: 'تأكيد كلمة المرور الجديدة' },

    // Hero
    hero_title_1: { en: 'Saudi', ar: 'السياحة' },
    hero_title_2: { en: 'Trip', ar: 'السعودية' },
    hero_title_3: { en: 'Management System', ar: 'بكل سهولة' },

    // Register features
    reg_feat1: { en: 'Access to hundreds of curated trips', ar: 'الوصول إلى مئات الرحلات المنظَّمة' },
    reg_feat2: { en: 'Connect with certified local guides', ar: 'التواصل مع مرشدين محليين معتمدين' },
    reg_feat3: { en: 'Secure booking and payment system', ar: 'نظام حجز ودفع آمن' },
    reg_feat4: { en: 'Rate and review your experiences', ar: 'قيّم تجاربك وشارك آراءك' },

    // Book page
    bk_date_fixed: { en: 'Fixed by the guide – cannot be changed', ar: 'محدد من المرشد — لا يمكن تغييره' },

    // Admin dashboard
    ad_col_type:   { en: 'Type', ar: 'النوع' },
    ad_col_delete: { en: 'Delete', ar: 'حذف' },

    // Guide dashboard (shared keys not in inline translations)
    gd_addons:         { en: 'Add-ons', ar: 'الإضافات' },
    gd_my_packages:    { en: 'My Packages', ar: 'باقاتي' },
    gd_packages_label: { en: 'Packages', ar: 'الباقات' },
    gd_pkg_bookings:   { en: 'Package Bookings', ar: 'حجوزات الباقات' },
    gd_sp_bookings:    { en: 'Self-Planned Bookings (Assigned to Me)', ar: 'حجوزات ذاتية التخطيط (المُعيَّنة لي)' },
    gd_create_pkg_nav: { en: 'Create Package', ar: 'إنشاء باقة' },
    gd_pkg_start_date: { en: 'Start Date', ar: 'تاريخ البداية' },
    gd_max_10:         { en: 'Max 10', ar: 'الحد الأقصى 10' },
};

// FAQ Arabic translations (keyed by English question text)
const faqTranslations = {
    'How do I book a trip?': {
        q: 'كيف أحجز رحلة؟',
        a: 'تصفح الرحلات المتاحة، اختر واحدة، حدد التاريخ وعدد الأشخاص، ثم اضغط على "احجز الرحلة".'
    },
    'Can I cancel a booking?': {
        q: 'هل يمكنني إلغاء حجز؟',
        a: 'نعم، يمكنك الإلغاء قبل 48 ساعة من موعد بدء الرحلة لاسترداد المبلغ بالكامل.'
    },
    'How do I become a tour guide?': {
        q: 'كيف أصبح مرشدًا سياحيًا؟',
        a: 'سجّل كمرشد، أكمل ملفك الشخصي، وابدأ بإنشاء الرحلات. سيراجع فريق الإدارة حسابك خلال 24 ساعة.'
    },
    'Is it safe to travel in Saudi Arabia?': {
        q: 'هل السفر في المملكة العربية السعودية آمن؟',
        a: 'المملكة العربية السعودية من أكثر الدول أمانًا للسياح في العالم. مرشدونا محترفون معتمدون.'
    },
    'What payment methods are accepted?': {
        q: 'ما هي طرق الدفع المقبولة؟',
        a: 'نقبل بطاقات الائتمان الرئيسية (فيزا، ماستركارد) والتحويلات البنكية المحلية ومدى.'
    },
    'How are guides verified?': {
        q: 'كيف يتم التحقق من المرشدين؟',
        a: 'يتم فحص جميع المرشدين والتحقق من هويتهم ومراجعة مؤهلاتهم قبل الموافقة عليهم.'
    },
    'Can I leave a review?': {
        q: 'هل يمكنني ترك تقييم؟',
        a: 'نعم! بعد إكمال رحلتك، يمكنك تقييم ومراجعة تجربتك لمساعدة المسافرين الآخرين.'
    },
    'What if I have a problem during the trip?': {
        q: 'ماذا لو واجهت مشكلة أثناء الرحلة؟',
        a: 'تواصل مع فريق الدعم لدينا على مدار الساعة عبر البريد الإلكتروني أو الهاتف وسنساعدك فورًا.'
    },
    'Are trips available year-round?': {
        q: 'هل الرحلات متاحة على مدار السنة؟',
        a: 'نعم، نقدم رحلات على مدار العام، مع وجهات ومواسم مختلفة تناسب جميع الأوقات.'
    },
    'Do I need a visa to visit Saudi Arabia?': {
        q: 'هل أحتاج تأشيرة لزيارة المملكة العربية السعودية؟',
        a: 'يمكن لمواطني العديد من الدول الحصول على تأشيرة سياحية إلكترونية أو عند الوصول. تحقق من متطلبات بلدك.'
    }
};

function translateFaqText(question, answer) {
    if (currentLang !== 'ar') return { q: question, a: answer };
    const match = faqTranslations[question];
    if (match) return match;
    // Try partial match
    for (const key in faqTranslations) {
        if (question.toLowerCase().includes(key.toLowerCase().substring(0, 20))) {
            return faqTranslations[key];
        }
    }
    return { q: question, a: answer };
}

let currentLang = localStorage.getItem('stms_lang') || 'en';

function toggleLang() {
    currentLang = currentLang === 'en' ? 'ar' : 'en';
    localStorage.setItem('stms_lang', currentLang);
    applyLang();
}

function applyLang() {
    const html = document.documentElement;
    if (currentLang === 'ar') {
        html.setAttribute('dir', 'rtl');
        html.setAttribute('lang', 'ar');
        document.body.classList.add('rtl');
    } else {
        html.setAttribute('dir', 'ltr');
        html.setAttribute('lang', 'en');
        document.body.classList.remove('rtl');
    }

    // Update lang toggle button text
    document.querySelectorAll('.lang-switch').forEach(btn => {
        btn.textContent = currentLang === 'en' ? 'العربية' : 'English';
    });

    // Translate data-i18n elements (textContent)
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[key] && translations[key][currentLang]) {
            el.textContent = translations[key][currentLang];
        }
    });

    // Translate data-i18n-html elements (innerHTML with icons)
    document.querySelectorAll('[data-i18n-html]').forEach(el => {
        const key = el.getAttribute('data-i18n-html');
        if (translations[key] && translations[key][currentLang]) {
            // Preserve leading icon if present
            const icon = el.querySelector('i');
            if (icon) {
                const iconHtml = icon.outerHTML;
                el.innerHTML = iconHtml + ' ' + translations[key][currentLang];
            } else {
                el.textContent = translations[key][currentLang];
            }
        }
    });

    // Translate data-i18n-placeholder (input placeholders)
    document.querySelectorAll('[data-i18n-ph]').forEach(el => {
        const key = el.getAttribute('data-i18n-ph');
        if (translations[key] && translations[key][currentLang]) {
            el.placeholder = translations[key][currentLang];
        }
    });
}

function t(key) {
    if (translations[key] && translations[key][currentLang]) {
        return translations[key][currentLang];
    }
    return translations[key] ? translations[key]['en'] : key;
}

// Allow other pages to extend translations
function addTranslations(extra) {
    Object.keys(extra).forEach(key => {
        translations[key] = extra[key];
    });
}

// Auto-apply language on page load
document.addEventListener('DOMContentLoaded', function() {
    applyLang();
});

// ===== Auth Check =====
async function checkAuth() {
    try {
        const res = await fetch('php/auth.php?action=check', { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.logged_in) {
            localStorage.setItem('stms_user', JSON.stringify(data.user));
            return data.user;
        }
        localStorage.removeItem('stms_user');
        return null;
    } catch (e) {
        return null;
    }
}

// ===== Logout =====
function handleLogout() {
    fetch('php/auth.php?action=logout', { credentials: 'include' })
        .then(r => r.json())
        .then(() => {
            localStorage.removeItem('stms_user');
            window.location.href = 'index.html';
        })
        .catch(() => {
            localStorage.removeItem('stms_user');
            window.location.href = 'index.html';
        });
}

// ===== Utility: Format Date =====
function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch (e) {
        return dateStr;
    }
}

// ===== Utility: Calculate Duration =====
function calcDuration(start, end) {
    if (!start || !end) return '';
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
    return diff === 1 ? '1 Day' : diff + ' Days';
}

// ===== Utility: Render Star Rating =====
function renderStars(rating) {
    rating = parseFloat(rating) || 0;
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars += '<i class="fas fa-star"></i>';
        } else if (i - 0.5 <= rating) {
            stars += '<i class="fas fa-star-half-alt"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars + ' <span style="color:var(--text-light); font-size:0.8rem;">' + rating.toFixed(1) + '</span>';
}

// ===== Modal =====
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('show');
}

// Close modal on overlay click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

// ===== FAQ Accordion Toggle =====
document.addEventListener('click', function (e) {
    const faqAnswer = e.target.closest('.faq-answer');
    if (faqAnswer) return;

    // Toggle faq-answer.active via max-height
    const answers = document.querySelectorAll('.faq-answer.active');
    // Handled inline via onclick in HTML
});

// Apply active class styling for FAQ
const style = document.createElement('style');
style.textContent = `
    .faq-answer.active {
        max-height: 300px !important;
        padding-bottom: 0 !important;
    }
`;
document.head.appendChild(style);

// ===== Responsive Sidebar =====
(function () {
    function checkWidth() {
        const toggleBtns = document.querySelectorAll('.mobile-toggle');
        if (window.innerWidth <= 768) {
            toggleBtns.forEach(btn => btn.style.display = 'block');
        } else {
            toggleBtns.forEach(btn => btn.style.display = 'none');
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.remove('open');
        }
    }
    window.addEventListener('resize', checkWidth);
    checkWidth();
})();
