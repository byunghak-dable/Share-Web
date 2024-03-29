<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share - board</title>
    <?php require('holder/main/linkCss.php'); ?>
    <link rel="stylesheet" href="public/css/mainBoardStyle.css?after">
    <script defer src="app/views/javascript/board.js"></script>
    <script>
        let isLogin = "<?= isset($_SESSION['userID']) ?>"
    </script>
</head>

<body>

    <?php require('holder/main/bodyHeadPage.php'); ?>

    <div class="boardContainer">
        <h1>자유게시판</h1>
        <table>
            <thead>
                <tr>
                    <th width="70">번호</th>
                    <th width="700">제목</th>
                    <th width="150">작성자</th>
                    <th width="120">작성일</th>
                    <th width="100">조회수</th>
                </tr>
            </thead>

            <?php foreach ($postList as $post) : ?>
                <tbody>
                    <tr>
                        <?php if ($post->getCommentCount() == 0) : ?>
                            <?php $commentCount = '' ?>
                        <?php else : ?>
                            <?php $commentCount = '[' . $post->getCommentCount() . ']' ?>
                        <?php endif ?>
                        <td width="70"><?= $post->getID() ?></td>
                        <td width="700"><a href=<?= '/board-read?id=' . $post->getID(); ?>><?= $post->getTitle() ?><p><?= ' ' . $commentCount ?></p></a>
                        </td>
                        <td width="150"><?= $post->getWriter() ?></td>
                        <td width="120"><?= $post->getDate() ?></td>
                        <td width="100"><?= $post->getHit() ?></td>
                    </tr>
                </tbody>
            <?php endforeach; ?>
        </table>

        <div class="pagination">

            <!-- 이전 버튼 생성 -->
            <?php if ($currentPage > 1) : ?>
                <a href=<?= '/board?page=' . ($currentPage - 1) ?>><button id="previousBtn">이전</button></a>
            <?php endif; ?>

            <!-- 페이징 버튼 생성 -->
            <?php for ($page = $startPage; $page <= $endPage; $page++) { ?>

                <?php if ($currentPage == $page) : ?>
                    <a href=<?= "/board?page={$page}" ?>><button class="active"><?= $page ?></button></a>
                <?php else : ?>
                    <a href=<?= "/board?page={$page}" ?>><button><?= $page ?></button></a>
                <?php endif; ?>

            <?php } ?>

            <!-- 다음 버튼 생성 -->
            <?php if ($currentPage < $numberOfPage) : ?>
                <a href=<?= '/board?page=' . ($currentPage + 1) ?>><button id="nextBtn">다음</button></a>
            <?php endif; ?>
        </div>

        <div class="wirte">
            <a href="/board-write"><button id="writeBtn">글쓰기</button></a>
        </div>
    </div>

</body>

</html>