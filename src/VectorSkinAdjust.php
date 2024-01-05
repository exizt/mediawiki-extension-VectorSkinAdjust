<?php
/**
 * VectorSkinAdjust
 *
 * @link https://github.com/exizt/mw-ext-VectorSkinAdjust
 * @author exizt
 * @license GPL-2.0-or-later
 */
class VectorSkinAdjust {

    /**
     * 'BeforePageDisplay' 훅.
     *
     * 페이지가 호출되는 시점 즈음에서 호출되는 훅. 대부분(거의 모든) 페이지에서 호출되고, 다양하게 사용하기 좋다.
     * css, js 등의 리소스 핸들링을 위한 훅.
     *
     * @param OutputPage $out
     * @param Skin $skin
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
     * @see https://github.com/WikiMANNia/MediaWiki-Extension-SkinCustomiser/blob/master/includes/specials/HooksSkinCustomiser.php
     */
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
        self::debugLog('::onBeforePageDisplay');

        # 벡터 스킨일 경우, 불편함을 fix하는 css 로드.
        self::customizeLegacyVectorSkin($skin, $out);
    }

    /**
     * 벡터 스킨 (레거시 버전에 한정)인 경우에 스킨 형태를 fix하는 부분.
     *
     * 반응형이 안 되거나 몇 가지 불편한 부분이 있으므로, 이를 fix하는 스크립트를 호출한다.
     *
     * 벡터 스킨 외에는 제작한 스킨을 사용할 것이므로 동작되지 않아도 됨.
     *
     * @param Skin $skin
     * @param OutputPage $out
     */
    private static function customizeLegacyVectorSkin(Skin $skin, OutputPage $out){
        $skinName = $skin->getSkinName();
        if($skinName != 'vector'){
            return;
        }

        // vector 스킨에서는 legacy에서만 적용.
        if(self::isLegacyVectorSkin($skin)){
            self::debugLog('is vector legacy skin');
            $moduleStyles[] = 'ext.vector-skin-adjust.legacy-fix';
            $out->addModuleStyles( $moduleStyles );
        }
    }

    /**
     * 레거시 버전의 Vector 인지 여부
     *
     * @param Skin $skin
     * @return bool 레거시 버전의 Vector 인지 여부
     */
    private static function isLegacyVectorSkin( Skin $skin ): bool{
        $skinName = $skin->getSkinName();
        if($skinName != 'vector') return false;

        // 버전별 코드 참조
        if(version_compare(MW_VERSION, '1.36', '>=')){
            // 1.36 버전 이상
            $options = $skin->getOptions();
            if( in_array('skins.vector.legacy.js', $options['scripts']) ){
                return true;
            }
        } else if(version_compare(MW_VERSION, '1.35', '>=')){
            // 1.35 버전
            $defaultModules = $skin->getDefaultModules();
            if( $defaultModules['vector'] == 'skins.vector.legacy.js'){
                return true;
            }
        }
        return false;
    }

    /**
     * 디버그 로깅 관련
     *
     * @param string|object $msg 디버깅 메시지 or 오브젝트
     */
    private static function debugLog($msg){
        global $wgDebugToolbar;

        # 디버그툴바 사용중일 때만 허용.
        $useDebugToolbar = $wgDebugToolbar ?? false;
        if( !$useDebugToolbar ){
            return false;
        }

        # 로깅
        if(is_string($msg)){
            wfDebugLog(static::class, $msg);
        } else {
            wfDebugLog(static::class, json_encode($msg));
        }
    }
}
