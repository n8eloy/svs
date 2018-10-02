<div class="pagination_footer">
    <?php
        $sideIdxAmount = 4;
        $totalIdxAmount = $sideIdxAmount * 2;

        // Limite inferior
        $idxDownLimit = ($page - $sideIdxAmount > 1 ? $page - $sideIdxAmount : 1);
        // Limite superior
        $idxUpLimit = ($page + $sideIdxAmount < $totalPages ? $page + $sideIdxAmount : $totalPages);

        // Quantidade fixa para pagination
        if($idxUpLimit - $idxDownLimit < $totalIdxAmount) {
            if($idxDownLimit == 1) {
                $idxUpLimit = $idxUpLimit + ($totalIdxAmount - ($idxUpLimit - $idxDownLimit));
                // Checar se passou do limite de páginas
                $idxUpLimit = ($idxUpLimit > $totalPages ? $totalPages : $idxUpLimit);
            } else {
                $idxDownLimit = $idxDownLimit - ($totalIdxAmount - ($idxUpLimit - $idxDownLimit));
            }
        }

        // Impressão de botões
        if($idxDownLimit >= 2) {
            if($page == 1) {
                ?><p class="pagination">1</p><?php
            } else {                         
                ?><a class="pagination" href="<?='?'.http_build_query(array_merge($_GET, array("page" => "1")))?>">1</a><?php
            }

            if($idxDownLimit > 2) {
                ?><p class="pagination"><?="..."?></p><?php
            }
        }

        for($i = ($idxDownLimit); $i <= $idxUpLimit; $i++) {
            if($i == $page) {
                ?><p class="pagination"><?=$i?></p><?php
            } else {                         
                ?><a class="pagination" href="<?='?'.http_build_query(array_merge($_GET, array("page" => $i)))?>"><?=$i?></a><?php
            }
        }
        
    
        if($idxUpLimit <= $totalPages - 1) {
            if($idxUpLimit < $totalPages - 1) {
                ?><p class="pagination"><?="..."?></p><?php
            }

            if($page == $totalPages) {
                ?><p class="pagination"><?=$totalPages?></p><?php
            } else {
                ?><a class="pagination" href="<?='?'.http_build_query(array_merge($_GET, array("page" => $totalPages)))?>"><?=$totalPages?></a><?php
            }
        }
    ?>
</div>