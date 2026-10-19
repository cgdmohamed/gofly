<?php
/**
 * Demo content mirroring the approved design.
 *
 * Images are referenced as:
 *   'u:<unsplash id>'   downloaded from images.unsplash.com at import time
 *   'f:<path>'          bundled file in assets/demo/
 *   'u:<id>|f:<path>'   try each in turn
 * Unreachable images fall back to a generated placeholder.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

$faq_home = array(
	array( 'question' => 'هل أحتاج إلى تأشيرة للسفر؟', 'answer' => 'يعتمد ذلك على جنسيتك والوجهة. نوضح المتطلبات لكل برنامج ونساعدك في تجهيز الأوراق.' ),
	array( 'question' => 'كيف أدفع وهل الأسعار شاملة؟', 'answer' => 'الأسعار المعلنة شاملة الضرائب، ونوضح خطوات الدفع بالتفصيل قبل تأكيد الحجز.' ),
	array( 'question' => 'هل يمكن تخصيص البرنامج؟', 'answer' => 'نعم، نعدّل البرامج حسب عدد الأفراد والميزانية وفئة الفندق.' ),
	array( 'question' => 'ما سياسة الإلغاء؟', 'answer' => 'تختلف حسب البرنامج والفندق، وتُعرض بوضوح قبل تأكيد الحجز.' ),
);

$russia = array(
	'subtitle'          => 'موسكو · جولات بسائق خاص · خدمة عملاء بالعربية 24 ساعة',
	'group_size'        => '1 – 15',
	'hotel_category'    => 'حسب التوفر',
	'cancellation_note' => 'البرنامج غير مسترد، ولا يُلغى الفندق مجانًا قبل أقل من 72 ساعة.',
	// High-resolution photos from Unsplash; the small bundled shots are only a fallback.
	'gallery'           => array( 'u:1513326738677-b964603b136d|f:russia/basil.jpg', 'u:1547448415-e9f5b28e570d|f:russia/metro.jpg', 'u:1520106212299-d99c443e4568|f:russia/city.jpg', 'u:1555939594-58d7cb561ad1|f:russia/airport.jpg' ),
	'itinerary_title'   => 'برنامج الرحلة',
	'itinerary'         => array(
		array( 'title' => 'الاستقبال والراحة', 'text' => 'الاستقبال في أحد مطارات موسكو والتوجه إلى الفندق، مع مشاهدة بعض معالم العاصمة في الطريق. استلام الغرف ويوم للراحة.', 'overnight' => 'مبيت موسكو' ),
		array( 'title' => 'جولات موسكو', 'text' => 'مترو موسكو بمحطاته المعمارية الفريدة، ثم شارع أربات القديم والتسوق، والغداء في أحد المطاعم، وبعدها معرض تريتياكوف الشهير.', 'overnight' => 'مبيت موسكو' ),
		array( 'title' => 'الساحة الحمراء', 'text' => 'زيارة أهم معالم روسيا: الساعة الكبيرة والقباب الملونة وضريح لينين والمجمع التجاري. وفي المساء السيرك الروسي.', 'overnight' => 'مبيت موسكو' ),
		array( 'title' => 'يوم للاستجمام', 'text' => 'يوم حر بدون جولات بالسيارة، لاكتشاف المنطقة القريبة من الفندق والتسوق قبل السفر.', 'overnight' => 'مبيت موسكو' ),
		array( 'title' => 'المغادرة', 'text' => 'بعد الإفطار التوجه إلى مطار موسكو الدولي مع تمنياتنا برحلة عودة سعيدة.', 'overnight' => '' ),
	),
	'includes'          => "الاستقبال والتوديع من وإلى المطار\nالإقامة 4 ليالٍ في موسكو\nشريحة إنترنت لكل فرد\nجولات سياحية 5 ساعات\nسائق خاص وسيارة خاصة والبترول\nخدمة عملاء بالعربية 24 ساعة\nالسعر شامل كافة الضرائب",
	'excludes'          => "وجبتا الغداء والعشاء\nتذاكر الطيران\nمواقف السيارات (Parking)\nكل ما لم يُذكر في العرض",
	'hotels_title'      => 'فنادق الإقامة في موسكو',
	'hotels_note'       => 'يحدد فريقنا الفندق المناسب حسب التوفر وقت الحجز، والإفطار مشمول.',
	'hotels'            => array(
		array( 'tier' => 'العرض المخفض', 'stars' => '4', 'name' => 'Aerostar Hotel Moscow', 'note' => 'طابع كلاسيكي · 2 كم من المركز' ),
		array( 'tier' => 'العرض المخفض', 'stars' => '4', 'name' => 'Izmailovo Delta Hotel', 'note' => 'طابع حديث · 1.1 كم من المركز' ),
		array( 'tier' => 'العرض الرائع', 'stars' => '4', 'name' => 'Hotel Art Moscow Voykovskaya', 'note' => 'طابع حديث · 8.5 كم من المركز' ),
		array( 'tier' => 'العرض المميز', 'stars' => '4', 'name' => 'Borodino Alliance Hotel', 'note' => 'طابع حديث · 3.5 كم من المركز' ),
		array( 'tier' => 'VIP', 'stars' => '4', 'name' => 'Hampton by Hilton', 'note' => 'طابع حديث · 3.5 كم من المركز' ),
		array( 'tier' => 'VIP 2', 'stars' => '5', 'name' => 'Mamaison All-Suites Spa Pokrovka', 'note' => 'مسبح داخلي · 2.5 كم من المركز' ),
		array( 'tier' => 'VIP 3', 'stars' => '5', 'name' => 'Hilton Moscow Leningradskaya', 'note' => 'كلاسيكي بمسبح داخلي · 3 كم من المركز' ),
	),
	'cars_title'        => 'السيارات المتاحة',
	'cars'              => array(
		array( 'image' => 'f:cars/elantra.png', 'capacity' => 'من 1 إلى 3 أشخاص', 'models' => 'Hyundai Elantra · Toyota Camry · Kia Soul' ),
		array( 'image' => 'f:cars/caravelle.jpg', 'capacity' => 'من 4 إلى 7 أشخاص', 'models' => 'Volkswagen Caravelle · Hyundai Grand Starex' ),
		array( 'image' => 'f:cars/sprinter.png', 'capacity' => 'من 8 إلى 15 شخصًا', 'models' => 'Mercedes Sprinter Minibus' ),
	),
	'rooms_title'       => 'تقسيم الغرف',
	'rooms'             => array(
		array( 'label' => 'فرد واحد', 'split' => 'غرفة مفردة' ),
		array( 'label' => 'فردان', 'split' => 'غرفة ثنائية' ),
		array( 'label' => '3 أفراد', 'split' => 'غرفة ثلاثية' ),
		array( 'label' => '4 أفراد', 'split' => '2 غرفة ثنائية' ),
		array( 'label' => '5 أفراد', 'split' => 'غرفة ثنائية + غرفة ثلاثية' ),
		array( 'label' => '6 أفراد', 'split' => '2 غرفة ثلاثية' ),
		array( 'label' => '7 أفراد', 'split' => '2 ثنائية + 1 ثلاثية' ),
		array( 'label' => '8 أفراد', 'split' => '1 ثنائية + 2 ثلاثية' ),
		array( 'label' => '9 أفراد', 'split' => '3 غرف ثلاثية' ),
		array( 'label' => '10 أفراد', 'split' => '2 ثنائية + 2 ثلاثية' ),
	),
	'rooms_note'        => 'الأطفال أقل من 5 سنوات مجانًا (طفل واحد كحد أقصى)، وأكبر من 5 سنوات يُحسب كبالغ.',
	'facts_title'       => 'معلومات عن روسيا',
	'facts'             => array(
		array( 'label' => 'العاصمة', 'value' => 'موسكو' ),
		array( 'label' => 'المساحة', 'value' => '17.1 مليون كم²' ),
		array( 'label' => 'السكان', 'value' => '143.8 مليون' ),
		array( 'label' => 'اللغة', 'value' => 'الروسية' ),
		array( 'label' => 'العملة', 'value' => 'الروبل (RUB)' ),
		array( 'label' => 'المناخ صيفًا', 'value' => '20–30° في موسكو' ),
	),
	'restaurants_title' => 'مطاعم حلال مقترحة في موسكو',
	'restaurants_note'  => 'الغداء والعشاء على حساب المسافر، والشركة توصلكم للمطاعم الحلال.',
	'restaurants'       => array(
		array( 'name' => 'Mister Livanets', 'cuisine' => 'مأكولات عربية', 'rating' => '4.4' ),
		array( 'name' => 'Kebab Russky', 'cuisine' => 'كباب روسي حلال', 'rating' => '4.4' ),
		array( 'name' => 'Mesopotamia', 'cuisine' => 'مطعم تركي', 'rating' => '4.2' ),
		array( 'name' => 'Pita-bar Meat Me', 'cuisine' => 'لبناني · عربي وتركي', 'rating' => '4.4' ),
		array( 'name' => 'Sheikh Palace', 'cuisine' => 'مطعم عربي', 'rating' => '4.7' ),
	),
	'policies'          => array(
		array( 'title' => 'سياسة الإلغاء', 'text' => 'البرنامج غير مسترد.' ),
		array( 'title' => 'الفنادق', 'text' => 'الإفطار بوفيه من 7 إلى 10 صباحًا. تسجيل الدخول بعد 2–3 ظهرًا والخروج قبل 12 ظهرًا. قد يُطلب مبلغ تأمين عند الدخول ويُسترد عند المغادرة. التأخر عن الخروج يحمّل المسافر أي تكاليف إضافية.' ),
		array( 'title' => 'الجولات السياحية', 'text' => 'الجولات المذكورة على سبيل المثال لا الحصر. إذا تعذّر أحد الأماكن لعطلة رسمية أو صيانة يتوجه السائق إلى جولة بديلة، ويُنسَّق مع السائق لأي طلبات شخصية.' ),
		array( 'title' => 'إجراءات السفر', 'text' => 'الجنسية الإماراتية والقطرية: لا تحتاج تأشيرة. الجنسية السعودية والعمانية والكويتية: تأشيرة إلكترونية. غير ذلك: مراجعة السفارة أو التأشيرة الإلكترونية حسب الجنسية.' ),
		array( 'title' => 'المعاملات المالية', 'text' => 'يُنصح بحمل مبلغ نقدي كافٍ بالدولار الأمريكي أو الروبل، فلا يمكن استخدام البطاقات البنكية داخل روسيا.' ),
		array( 'title' => 'قبل الحجز', 'text' => 'يُرجى الاطلاع على تفاصيل الفندق وصوره وآراء العملاء على Booking. لا يمكن تغيير الفندق بعد الوصول إلا لأعطال أو أسباب واضحة، وغالبًا لا يُلغى مجانًا قبل أقل من 72 ساعة.' ),
	),
);

$moscow_article = '<!-- wp:heading --><h2>مقدمة</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>موسكو مدينة تجمع بين العمارة التاريخية والحياة العصرية، وخمسة أيام كافية لتغطية أهم معالمها دون استعجال. في هذا الدليل نرتب لك الأيام بحيث تتنقل بسهولة وتستمتع بأفضل ما في العاصمة الروسية.</p><!-- /wp:paragraph -->
<!-- wp:quote --><blockquote class="wp-block-quote"><p>أفضل ما في موسكو لا يظهر في يوم واحد، لذلك خصصنا يومًا كاملًا للراحة وسط الجولات.</p></blockquote><!-- /wp:quote -->
<!-- wp:heading --><h2>برنامج الأيام الخمسة</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>اليوم الأول:</strong> الاستقبال في المطار والتوجه إلى الفندق ويوم للراحة.</li><li><strong>اليوم الثاني:</strong> مترو موسكو، شارع أربات القديم، ثم معرض تريتياكوف.</li><li><strong>اليوم الثالث:</strong> الساحة الحمراء والكرملين، وفي المساء السيرك الروسي.</li><li><strong>اليوم الرابع:</strong> يوم حر للتسوق واستكشاف المنطقة القريبة من الفندق.</li><li><strong>اليوم الخامس:</strong> المغادرة بعد الإفطار.</li></ul><!-- /wp:list -->
<!-- wp:heading --><h2>الساحة الحمراء</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>تُعد الساحة الحمراء أهم معلم في روسيا، وتضم الساعة الكبيرة والقباب الملونة والمجمع التجاري التاريخي. يُفضَّل الذهاب صباحًا لتجنب الازدحام، ثم العودة مساءً لرؤية الإضاءة.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>مطاعم حلال مقترحة</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>تتوفر في موسكو مطاعم حلال متنوعة، منها <strong>Mister Livanets</strong> للمأكولات العربية، و<strong>Kebab Russky</strong> للكباب الروسي، و<strong>Sheikh Palace</strong> الذي يحمل تقييمًا مرتفعًا. ننسق لك التوصيل إلى المطاعم ضمن برنامجنا.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>نصائح قبل السفر</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li>احمل مبلغًا نقديًا كافيًا بالدولار أو الروبل، فالبطاقات البنكية لا تعمل داخل روسيا.</li><li>تأكد من متطلبات التأشيرة حسب جنسيتك قبل الحجز.</li><li>ارتدِ ملابس مناسبة للطقس، فالشتاء شديد البرودة والصيف دافئ.</li></ul><!-- /wp:list -->';

return array(
	'options'      => array(
		'stats'      => array(
			array( 'value' => '+12', 'label' => 'سنة خبرة' ),
			array( 'value' => '+40', 'label' => 'وجهة حول العالم' ),
			array( 'value' => '98%', 'label' => 'رضا العملاء' ),
			array( 'value' => '+12,000', 'label' => 'مسافر سعيد' ),
		),
		'social'     => array(
			array( 'network' => 'instagram', 'url' => 'https://instagram.com/' ),
			array( 'network' => 'x-twitter', 'url' => 'https://x.com/' ),
			array( 'network' => 'snapchat', 'url' => 'https://snapchat.com/' ),
			array( 'network' => 'tiktok', 'url' => 'https://tiktok.com/' ),
			array( 'network' => 'youtube', 'url' => 'https://youtube.com/' ),
		),
		'partners'   => array_map( fn( $f ) => 'f:partners/' . $f, array( 'accor.png', 'airarabia.png', 'aroya.png', 'costa.png', 'crystal.png', 'emarats.png', 'etihad.png', 'explora.png', 'flydubi.png', 'flynas.png', 'gulfair.png', 'hilton.png', 'hyat.png', 'jumirah.png', 'marriot.png', 'oman-air.png', 'qatar.png', 'saudia.png' ) ),
		'header_cta' => array( 'page' => 'booking', 'title' => 'احجز الآن' ),
		'map_link'   => 'https://maps.google.com/?q=Riyadh',
	),

	'destinations' => array(
		'paris'    => array( 'باريس', 'فرنسا', 'u:1499856871958-5b9627545d1a' ),
		'istanbul' => array( 'اسطنبول', 'تركيا', 'u:1524231757912-21f4fe3a7200' ),
		'maldives' => array( 'المالديف', 'جزر المالديف', 'u:1514282401047-d79a71a590e8' ),
		'georgia'  => array( 'جورجيا', 'تبليسي والقوقاز', 'u:1565008576549-57569a49371d' ),
		'moscow'   => array( 'موسكو', 'روسيا', 'u:1513326738677-b964603b136d' ),
		'alps'     => array( 'جبال الألب', 'سويسرا', 'u:1464822759023-fed622ff2c3b' ),
		'bali'     => array( 'بالي', 'إندونيسيا', 'u:1507525428034-b723cf961d3e' ),
		'gulf'     => array( 'الخليج', 'رحلات بحرية', 'u:1469854523086-cc02fe5d8800' ),
	),

	'categories'   => array(
		'europe'    => array( 'أوروبا', 'castle' ),
		'asia'      => array( 'آسيا', 'temple_buddhist' ),
		'honeymoon' => array( 'شهر العسل', 'favorite' ),
		'family'    => array( 'عائلية', 'family_restroom' ),
		'cruise'    => array( 'رحلات بحرية', 'directions_boat' ),
	),

	// title, destination, days, nights, price, old price, rating, categories, image, extra fields.
	'packages'     => array(
		'russia'   => array( 'برنامج روسيا السياحي', 'moscow', 5, 4, 3200, 4000, 4.9, array( 'europe', 'family' ), 'u:1513326738677-b964603b136d|f:russia/basil.jpg', $russia ),
		'paris'    => array( 'باريس: مدينة النور', 'paris', 6, 5, 5300, 0, 4.9, array( 'europe', 'honeymoon' ), 'u:1499856871958-5b9627545d1a', array() ),
		'maldives' => array( 'جزر المالديف الفاخرة', 'maldives', 5, 4, 6600, 0, 5.0, array( 'asia', 'honeymoon' ), 'u:1514282401047-d79a71a590e8', array() ),
		'istanbul' => array( 'سحر اسطنبول', 'istanbul', 7, 6, 3700, 0, 4.8, array( 'europe', 'family' ), 'u:1524231757912-21f4fe3a7200', array() ),
		'georgia'  => array( 'جورجيا الجبلية', 'georgia', 6, 5, 2600, 0, 4.7, array( 'asia', 'family' ), 'u:1565008576549-57569a49371d', array() ),
		'alps'     => array( 'جبال سويسرا', 'alps', 7, 6, 7400, 0, 4.9, array( 'europe', 'honeymoon' ), 'u:1464822759023-fed622ff2c3b', array() ),
		'bali'     => array( 'بالي الاستوائية', 'bali', 8, 7, 4800, 0, 4.8, array( 'asia', 'honeymoon', 'family' ), 'u:1507525428034-b723cf961d3e', array() ),
		'med'      => array( 'رحلة بحرية في المتوسط', 'alps', 7, 6, 5900, 0, 4.6, array( 'europe', 'cruise' ), 'u:1507525428034-b723cf961d3e', array() ),
		'gulf'     => array( 'رحلة بحرية في الخليج', 'gulf', 4, 3, 2900, 0, 4.7, array( 'asia', 'cruise', 'family' ), 'u:1469854523086-cc02fe5d8800', array() ),
	),

	// title, category, excerpt, image, date, content (optional).
	'posts'        => array(
		array( 'التأشيرات: ما تحتاج معرفته قبل السفر', 'دليل', 'ملخص لأنواع التأشيرات ومدة الإصدار والأوراق المطلوبة.', 'u:1488646953014-85cb44e25828', '2026-09-01' ),
		array( 'كيف تختار الفندق المناسب لعائلتك', 'نصائح', 'معايير الموقع والإفطار والغرف العائلية قبل تأكيد الحجز.', 'u:1566073771259-6a8506099945', '2026-09-09' ),
		array( 'دليل موسكو في خمسة أيام: ماذا تزور وأين تأكل', 'وجهات', 'أهم المعالم والمطاعم الحلال وطرق التنقل في العاصمة الروسية، مع جدول مقترح لكل يوم.', 'u:1513326738677-b964603b136d', '2026-09-18', $moscow_article, array( 'موسكو', 'روسيا', 'حلال', 'دليل سفر' ) ),
		array( 'قائمة أساسية لحقيبة السفر الذكية', 'دليل', 'ما تحتاجه فعلًا في الحقيبة، وما يمكنك تركه في المنزل.', 'u:1553531384-cc64ac80f931', '2026-09-26' ),
		array( 'أفضل الأوقات لزيارة جزر المالديف', 'وجهات', 'دليل مختصر للمواسم والطقس والأسعار قبل أن تحدد موعد رحلتك.', 'u:1514282401047-d79a71a590e8', '2026-10-02' ),
		array( 'كيف تحجز رحلتك إلى باريس بأقل تكلفة', 'نصائح', 'خطوات عملية لاختيار التوقيت والفندق وتوفير المصاريف دون التضحية بالتجربة.', 'u:1436491865332-7a61a109cc05', '2026-10-10' ),
	),

	// key => title, template, subtitle, extra fields.
	'pages'        => array(
		'home'         => array( 'الرئيسية', '', '', array() ),
		'blog'         => array( 'المدونة', '', 'مقالات ونصائح تساعدك على التخطيط لرحلتك القادمة.', array() ),
		'destinations' => array( 'وجهات مختارة بعناية', 'destinations', 'اكتشف أجمل المدن والوجهات حول العالم، ثم اختر رحلتك.', array() ),
		'booking'      => array( 'احجز الآن', 'booking', 'طيران، فنادق، أو الاثنان معًا، ونرسل لك أفضل عرض عبر واتساب.', array() ),
		'contact'      => array(
			'تواصل معنا',
			'contact',
			'فريقنا جاهز للرد على استفساراتك وتجهيز عرض مخصص لك.',
			array(
				'faq_title' => 'قبل أن تتواصل',
				'faq_items' => array(
					array( 'question' => 'كم يستغرق الرد على طلبي؟', 'answer' => 'نرد غالبًا خلال دقائق عبر واتساب، وخلال ساعات العمل المعلنة.' ),
					array( 'question' => 'هل يمكن تخصيص البرنامج؟', 'answer' => 'نعم، نعدّل البرامج حسب عدد الأفراد والميزانية وفئة الفندق.' ),
					array( 'question' => 'ما سياسة الإلغاء؟', 'answer' => 'تختلف حسب البرنامج والفندق، وتُوضَّح قبل تأكيد الحجز.' ),
				),
			),
		),
		'about'        => array(
			'من نحن',
			'about',
			'تعرف على جو فلاي للسفر والسياحة.',
			array(
				'story_image'  => 'u:1488646953014-85cb44e25828',
				'story_button' => array( 'page' => 'contact', 'title' => 'تواصل معنا' ),
				'values_items' => array(
					array( 'title' => 'موثوقية', 'icon' => 'verified', 'text' => 'التزام بالمواعيد والبرامج كما وعدنا.' ),
					array( 'title' => 'شفافية', 'icon' => 'payments', 'text' => 'أسعار واضحة شاملة الضرائب بلا مفاجآت.' ),
					array( 'title' => 'دعم دائم', 'icon' => 'support_agent', 'text' => 'فريق عربي متاح على مدار الساعة.' ),
					array( 'title' => 'عناية بالتفاصيل', 'icon' => 'favorite', 'text' => 'من الاستقبال في المطار حتى العودة.' ),
				),
			),
		),
	),

	'home'         => array(
		'hero_button'       => array( 'archive' => 'package', 'title' => 'استكشف الباقات' ),
		'hero_image_main'   => 'u:1514282401047-d79a71a590e8',
		'hero_image_card'   => 'u:1524231757912-21f4fe3a7200',
		'hero_image_circle' => 'u:1499856871958-5b9627545d1a',
		'services_items'    => array(
			array( 'title' => 'حجز تذاكر الطيران', 'icon' => 'flight', 'text' => 'لجميع وجهات العالم والرحلات الداخلية بأسعار مناسبة.', 'link' => array( 'page' => 'booking' ) ),
			array( 'title' => 'حجز فنادق فاخرة', 'icon' => 'hotel', 'text' => 'فنادق مختارة بعناية تناسب ذوقك وميزانيتك.', 'link' => array( 'page' => 'booking' ) ),
			array( 'title' => 'إصدار رخص دولية', 'icon' => 'card_membership', 'text' => 'نجهّز لك رخصة القيادة الدولية بخطوات سهلة وسريعة.', 'link' => array( 'page' => 'contact' ) ),
			array( 'title' => 'حجز سيارات', 'icon' => 'directions_car', 'text' => 'سيارات بسائق خاص أو للإيجار حسب عدد المسافرين.', 'link' => array( 'page' => 'contact' ) ),
			array( 'title' => 'برامج سياحية عائلية', 'icon' => 'family_restroom', 'text' => 'برامج مريحة ومتوازنة تناسب جميع أفراد العائلة.', 'link' => array( 'cat' => 'family' ) ),
			array( 'title' => 'برامج شهر العسل', 'icon' => 'favorite', 'text' => 'رحلات رومانسية بتفاصيل مميزة تبقى في الذاكرة.', 'link' => array( 'cat' => 'honeymoon' ) ),
		),
		'dest_terms'        => array( 'paris', 'istanbul', 'maldives', 'georgia' ),
		'pkgs_items'        => array( 'russia', 'maldives', 'istanbul' ),
		'steps_items'       => array(
			array( 'title' => 'أخبرنا برحلتك', 'icon' => 'chat', 'text' => 'تواصل عبر واتساب أو النموذج وأخبرنا بوجهتك وعدد المسافرين.' ),
			array( 'title' => 'استلم عرض السعر', 'icon' => 'request_quote', 'text' => 'نجهّز لك عرضًا مخصصًا بالفندق والجولات خلال دقائق.' ),
			array( 'title' => 'أكّد حجزك', 'icon' => 'verified', 'text' => 'راجع التفاصيل واعتمد البرنامج بخطوات دفع واضحة.' ),
			array( 'title' => 'سافر باطمئنان', 'icon' => 'flight_takeoff', 'text' => 'استقبال في المطار وسائق ومرشد ودعم على مدار الساعة.' ),
		),
		'gallery_images'    => array( 'u:1499856871958-5b9627545d1a', 'u:1507525428034-b723cf961d3e', 'u:1524231757912-21f4fe3a7200', 'u:1464822759023-fed622ff2c3b', 'u:1414235077428-338989a2e8c0', 'u:1565008576549-57569a49371d' ),
		'insta_url'         => 'https://instagram.com/',
		'insta_images'      => array( 'u:1499856871958-5b9627545d1a', 'u:1514282401047-d79a71a590e8', 'u:1513326738677-b964603b136d', 'u:1524231757912-21f4fe3a7200', 'u:1464822759023-fed622ff2c3b', 'u:1565008576549-57569a49371d' ),
		'testi_items'       => array(
			array( 'name' => 'سارة العتيبي', 'trip' => 'رحلة اسطنبول', 'rating' => '5', 'avatar' => 'u:1494790108377-be9c29b29330', 'quote' => 'تنظيم رائع وكل شيء كان في موعده. أفضل رحلة عائلية قمنا بها.' ),
			array( 'name' => 'محمد الحربي', 'trip' => 'رحلة المالديف', 'rating' => '5', 'avatar' => 'u:1500648767791-00dcc994a43e', 'quote' => 'المرشد محترف جدًا والفنادق فوق التوقعات. سأحجز معهم مجددًا.' ),
			array( 'name' => 'نورة القحطاني', 'trip' => 'رحلة جورجيا', 'rating' => '5', 'avatar' => 'u:1534528741775-53994a69daeb', 'quote' => 'أسعار واضحة بلا مفاجآت، وفريق الدعم رد علينا فورًا.' ),
		),
		'faq_button'        => array( 'page' => 'contact', 'title' => 'تواصل معنا' ),
		'faq_items'         => $faq_home,
	),

	// Menu items: [title, link, children?]; links use the same keys as above.
	'menus'        => array(
		'primary'      => array(
			'name'  => 'القائمة الرئيسية',
			'items' => array(
				array( 'الرئيسية', array( 'home' => 1 ) ),
				array(
					'الوجهات',
					array( 'page' => 'destinations' ),
					array(
						array( 'باريس', array( 'dest' => 'paris' ), 'location_on', 'فرنسا' ),
						array( 'اسطنبول', array( 'dest' => 'istanbul' ), 'location_on', 'تركيا' ),
						array( 'المالديف', array( 'dest' => 'maldives' ), 'location_on', 'جزر المالديف' ),
						array( 'جورجيا', array( 'dest' => 'georgia' ), 'location_on', 'تبليسي' ),
						array( 'موسكو', array( 'package' => 'russia' ), 'location_on', 'روسيا' ),
						array( 'جبال الألب', array( 'dest' => 'alps' ), 'location_on', 'سويسرا' ),
					),
				),
				array(
					'الباقات',
					array( 'archive' => 'package' ),
					array(
						array( 'برامج سياحية عائلية', array( 'cat' => 'family' ), 'family_restroom' ),
						array( 'برامج شهر العسل', array( 'cat' => 'honeymoon' ), 'favorite' ),
						array( 'حجز فنادق فاخرة', array( 'page' => 'booking' ), 'hotel' ),
						array( 'حجز سيارات', array( 'page' => 'contact' ), 'directions_car' ),
						array( 'حجز تذاكر الطيران', array( 'page' => 'booking' ), 'flight' ),
						array( 'إصدار رخص دولية', array( 'page' => 'contact' ), 'card_membership' ),
					),
				),
				array( 'المدونة', array( 'page' => 'blog' ) ),
				array( 'من نحن', array( 'page' => 'about' ) ),
				array( 'تواصل معنا', array( 'page' => 'contact' ) ),
			),
		),
		'footer_1'     => array(
			'name'  => 'روابط سريعة',
			'items' => array(
				array( 'من نحن', array( 'page' => 'about' ) ),
				array( 'الباقات السياحية', array( 'archive' => 'package' ) ),
				array( 'المدونة', array( 'page' => 'blog' ) ),
				array( 'تواصل معنا', array( 'page' => 'contact' ) ),
			),
		),
		'footer_2'     => array(
			'name'  => 'الوجهات',
			'items' => array(
				array( 'روسيا', array( 'package' => 'russia' ) ),
				array( 'فرنسا', array( 'dest' => 'paris' ) ),
				array( 'تركيا', array( 'dest' => 'istanbul' ) ),
				array( 'المالديف', array( 'dest' => 'maldives' ) ),
				array( 'جورجيا', array( 'dest' => 'georgia' ) ),
			),
		),
		'footer_3'     => array(
			'name'  => 'خدماتنا',
			'items' => array(
				array( 'حجز تذاكر الطيران', array( 'page' => 'booking' ) ),
				array( 'حجز فنادق فاخرة', array( 'page' => 'booking' ) ),
				array( 'إصدار رخص دولية', array( 'page' => 'contact' ) ),
				array( 'حجز سيارات', array( 'page' => 'contact' ) ),
				array( 'برامج عائلية وشهر العسل', array( 'cat' => 'family' ) ),
			),
		),
		'footer_legal' => array(
			'name'  => 'الروابط القانونية',
			'items' => array(
				array( 'سياسة الخصوصية', array( 'privacy' => 1 ) ),
				array( 'الشروط والأحكام', array( 'url' => '#' ) ),
				array( 'سياسة الإلغاء', array( 'url' => '#' ) ),
			),
		),
	),
);
